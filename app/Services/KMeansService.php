<?php

namespace App\Services;

use App\Models\HasilCluster;
use App\Models\PeriodeAnalisis;
use App\Models\RekapGiziDesa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KMeansService
{
    protected array $data = [];
    protected int $k;
    protected int $maxIterations;
    protected array $centroids = [];
    protected array $minMax = [];
    /** @var string[] Desa yang dilewati karena data belum lengkap */
    protected array $skipped = [];

    // Fitur agregat desa (persentase) — satu titik data = satu desa
    protected array $criteria = [
        'cakupan_penimbangan',
        'persentase_stunting',
        'persentase_gizi_kurang',
        'persentase_bb_kurang',
    ];

    // Bobot skor risiko untuk labelling cluster
    protected array $riskWeights = [
        'persentase_stunting' => 0.5,
        'persentase_gizi_kurang' => 0.3,
        'persentase_bb_kurang' => 0.2,
    ];

    // Label cluster berdasarkan tingkat risiko
    public const CLUSTER_LABELS = [
        0 => 'Risiko Rendah',
        1 => 'Risiko Sedang',
        2 => 'Risiko Tinggi',
    ];

    public function __construct(int $k = 3, int $maxIterations = 100)
    {
        $this->k = $k;
        $this->maxIterations = $maxIterations;
    }

    /**
     * Menjalankan analisis K-Means dari data rekap agregat desa.
     * Filter: ['periode' => 'YYYY-MM'] (wajib), opsional ['desa_id' => int].
     */
    public function runAnalysis(array $filters = [], string $judul = ''): PeriodeAnalisis
    {
        $this->data = $this->fetchRekapData($filters);

        if (count($this->data) < $this->k) {
            throw new \Exception('Jumlah desa lengkap (' . count($this->data) . ") tidak cukup untuk {$this->k} cluster. Minimal {$this->k} desa dengan data lengkap diperlukan.");
        }

        $result = $this->performClustering();

        return $this->saveResults($result, $judul, $filters);
    }

    public function getSkipped(): array
    {
        return $this->skipped;
    }

    /**
     * Mengambil rekap agregat per desa dan menghitung fitur persentase.
     */
    protected function fetchRekapData(array $filters = []): array
    {
        $periode = $filters['periode'] ?? null;
        if (empty($periode) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $periode)) {
            throw new \Exception('Periode data wajib diisi dengan format YYYY-MM (misal 2026-01).');
        }

        $query = RekapGiziDesa::query()->with('desa')->where('periode', $periode);

        if (! empty($filters['desa_id'])) {
            $query->where('desa_id', (int) $filters['desa_id']);
        }

        $rekaps = $query->orderBy('id')->get();

        $data = [];
        $this->skipped = [];
        foreach ($rekaps as $r) {
            if (! $r->isLengkap()) {
                $this->skipped[] = [
                    'desa_id' => $r->desa_id,
                    'nama_desa' => $r->desa->nama_desa ?? 'Unknown',
                    'alasan' => 'Indikator stunting/gizi kurang/BB kurang belum lengkap (NULL)',
                ];
                continue;
            }
            $vector = $r->toFeatureVector();
            if ($vector === null) {
                continue;
            }

            $data[] = [
                'rekap_id' => $r->id,
                'desa_id' => $r->desa_id,
                'desa_nama' => $r->desa->nama_desa ?? 'Unknown',
                'periode' => $r->periode,
                'jumlah_balita' => $r->jumlah_balita,
                'jumlah_ditimbang' => $r->jumlah_ditimbang,
                'jumlah_stunting' => $r->jumlah_stunting,
                'jumlah_gizi_kurang' => $r->jumlah_gizi_kurang,
                'jumlah_bb_kurang' => $r->jumlah_bb_kurang,
                'cakupan_penimbangan' => $vector['cakupan_penimbangan'],
                'persentase_stunting' => $vector['persentase_stunting'],
                'persentase_gizi_kurang' => $vector['persentase_gizi_kurang'],
                'persentase_bb_kurang' => $vector['persentase_bb_kurang'],
                'skor_risiko' => $r->skor_risiko ?? $this->riskScore($vector),
            ];
        }

        return $data;
    }

    protected function riskScore(array $vector): float
    {
        $score = 0;
        foreach ($this->riskWeights as $key => $w) {
            $score += ($vector[$key] ?? 0) * $w;
        }

        return round($score, 2);
    }

    /**
     * Menjalankan algoritma K-Means clustering
     */
    public function performClustering(): array
    {
        $normalizedData = $this->normalizeData($this->data);

        $this->centroids = $this->initializeCentroidsKMeansPlusPlus($normalizedData);

        $iteration = 0;
        $prevCentroids = [];
        $clusters = [];

        while ($iteration < $this->maxIterations) {
            $prevCentroids = $this->centroids;
            $clusters = array_fill(0, $this->k, []);

            foreach ($normalizedData as $key => $point) {
                $closestCentroidIndex = $this->getClosestCentroid($point);
                $clusters[$closestCentroidIndex][] = $key;
            }

            $this->centroids = $this->updateCentroids($clusters, $normalizedData);

            if ($this->hasConverged($prevCentroids, $this->centroids)) {
                break;
            }

            $iteration++;
        }

        // Label cluster berdasarkan skor risiko (bukan nomor mentah K-Means)
        $labeledClusters = $this->labelClusters($clusters);

        return [
            'centroids' => $this->denormalizeCentroids($this->centroids),
            'centroids_normalized' => $this->centroids,
            'clusters' => $labeledClusters,
            'iterations' => $iteration + 1,
            'data_count' => count($this->data),
            'skipped' => $this->skipped,
        ];
    }

    /**
     * Menyimpan hasil clustering ke database
     */
    protected function saveResults(array $result, string $judul, array $filters): PeriodeAnalisis
    {
        return DB::transaction(function () use ($result, $judul, $filters) {
            $periode = $filters['periode'] ?? date('Y-m');

            if (empty($judul)) {
                try {
                    $namaBulan = Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y');
                } catch (\Exception) {
                    $namaBulan = $periode;
                }
                $judul = "Analisis Risiko Gizi {$namaBulan}";
            }

            // Snapshot fitur agar histori tidak berubah saat rekap diedit
            $snapshot = [];
            foreach ($this->data as $d) {
                $snapshot[] = [
                    'rekap_id' => $d['rekap_id'],
                    'desa_id' => $d['desa_id'],
                    'desa_nama' => $d['desa_nama'],
                    'cakupan_penimbangan' => $d['cakupan_penimbangan'],
                    'persentase_stunting' => $d['persentase_stunting'],
                    'persentase_gizi_kurang' => $d['persentase_gizi_kurang'],
                    'persentase_bb_kurang' => $d['persentase_bb_kurang'],
                    'skor_risiko' => $d['skor_risiko'],
                ];
            }

            $periode_analisis = PeriodeAnalisis::create([
                'user_id' => Auth::id(),
                'judul' => $judul,
                'periode_data' => $periode,
                'tanggal_proses' => now(),
                'jumlah_cluster' => $this->k,
                'total_data' => $result['data_count'],
                'data_centroid' => $result['centroids'],
                'data_snapshot' => $snapshot,
            ]);

            $normalized = $this->normalizeData($this->data);

            foreach ($result['clusters'] as $clusterIndex => $dataIndices) {
                foreach ($dataIndices as $dataIndex) {
                    $originalData = $this->data[$dataIndex];

                    HasilCluster::create([
                        'periode_analisis_id' => $periode_analisis->id,
                        'rekap_gizi_desa_id' => $originalData['rekap_id'],
                        'cluster' => $clusterIndex,
                        'kategori' => self::getClusterLabel($clusterIndex),
                        'jarak_centroid' => $this->euclideanDistance(
                            $normalized[$dataIndex],
                            $this->centroids[$clusterIndex]
                        ),
                        'skor_risiko' => $originalData['skor_risiko'],
                    ]);
                }
            }

            return $periode_analisis;
        });
    }

    protected function euclideanDistance(array $point1, array $point2): float
    {
        $sum = 0;
        foreach ($this->criteria as $metric) {
            $sum += pow(($point1[$metric] - $point2[$metric]), 2);
        }

        return sqrt($sum);
    }

    protected function getClosestCentroid(array $point): int
    {
        $minDistance = INF;
        $closestIndex = 0;

        foreach ($this->centroids as $index => $centroid) {
            $distance = $this->euclideanDistance($point, $centroid);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestIndex = $index;
            }
        }

        return $closestIndex;
    }

    protected function updateCentroids(array $clusters, array $data): array
    {
        $newCentroids = [];

        foreach ($clusters as $clusterIndex => $dataIndices) {
            if (empty($dataIndices)) {
                $newCentroids[$clusterIndex] = $this->centroids[$clusterIndex];
                continue;
            }

            $sums = array_fill_keys($this->criteria, 0);
            $count = count($dataIndices);

            foreach ($dataIndices as $index) {
                foreach ($this->criteria as $metric) {
                    $sums[$metric] += $data[$index][$metric];
                }
            }

            $newCentroids[$clusterIndex] = [];
            foreach ($this->criteria as $metric) {
                $newCentroids[$clusterIndex][$metric] = $sums[$metric] / $count;
            }
        }

        return $newCentroids;
    }

    protected function hasConverged(array $prev, array $current, float $threshold = 0.0001): bool
    {
        foreach ($current as $i => $centroid) {
            foreach ($this->criteria as $key) {
                if (abs($centroid[$key] - $prev[$i][$key]) > $threshold) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function normalizeData(array $data): array
    {
        $min = array_fill_keys($this->criteria, INF);
        $max = array_fill_keys($this->criteria, -INF);

        foreach ($data as $d) {
            foreach ($this->criteria as $key) {
                if ($d[$key] < $min[$key]) {
                    $min[$key] = $d[$key];
                }
                if ($d[$key] > $max[$key]) {
                    $max[$key] = $d[$key];
                }
            }
        }

        $this->minMax = ['min' => $min, 'max' => $max];

        $normalized = [];
        foreach ($data as $key => $d) {
            $row = $d;
            foreach ($this->criteria as $k) {
                $divisor = ($max[$k] - $min[$k]);
                $row[$k] = $divisor == 0 ? 0 : ($d[$k] - $min[$k]) / $divisor;
            }
            $normalized[$key] = $row;
        }

        return $normalized;
    }

    protected function denormalizeCentroids(array $centroids): array
    {
        $denormalized = [];
        foreach ($centroids as $i => $centroid) {
            $denormalized[$i] = [];
            foreach ($this->criteria as $key) {
                $range = $this->minMax['max'][$key] - $this->minMax['min'][$key];
                $denormalized[$i][$key] = ($centroid[$key] * $range) + $this->minMax['min'][$key];
            }
        }

        return $denormalized;
    }

    protected function initializeCentroidsKMeansPlusPlus(array $data): array
    {
        $centroids = [];

        $firstIndex = array_rand($data);
        $centroids[0] = [];
        foreach ($this->criteria as $key) {
            $centroids[0][$key] = $data[$firstIndex][$key];
        }

        for ($c = 1; $c < $this->k; $c++) {
            $distances = [];
            $totalDistance = 0;

            foreach ($data as $key => $point) {
                $minDist = INF;
                foreach ($centroids as $centroid) {
                    $dist = $this->euclideanDistance($point, $centroid);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                    }
                }
                $distances[$key] = $minDist * $minDist;
                $totalDistance += $distances[$key];
            }

            if ($totalDistance == 0) {
                $randKey = array_rand($data);
                $centroids[$c] = [];
                foreach ($this->criteria as $k) {
                    $centroids[$c][$k] = $data[$randKey][$k];
                }
                continue;
            }

            $random = mt_rand() / mt_getrandmax() * $totalDistance;
            $cumulative = 0;
            $picked = false;

            foreach ($distances as $key => $dist) {
                $cumulative += $dist;
                if ($cumulative >= $random) {
                    $centroids[$c] = [];
                    foreach ($this->criteria as $k) {
                        $centroids[$c][$k] = $data[$key][$k];
                    }
                    $picked = true;
                    break;
                }
            }

            if (! $picked) {
                $randKey = array_rand($data);
                $centroids[$c] = [];
                foreach ($this->criteria as $k) {
                    $centroids[$c][$k] = $data[$randKey][$k];
                }
            }
        }

        return $centroids;
    }

    /**
     * Melabeli cluster berdasarkan skor risiko tertimbang.
     * Skor terendah = Risiko Rendah (0), tertinggi = Risiko Tinggi.
     */
    protected function labelClusters(array $clusters): array
    {
        $clusterStats = [];

        foreach ($clusters as $clusterIndex => $dataIndices) {
            $totalScore = 0;
            $count = count($dataIndices);

            foreach ($dataIndices as $index) {
                $totalScore += $this->data[$index]['skor_risiko'] ?? 0;
            }

            $clusterStats[$clusterIndex] = [
                'avg_score' => $count > 0 ? $totalScore / $count : 0,
                'indices' => $dataIndices,
            ];
        }

        // Sort ascending — skor rendah = Risiko Rendah (label 0)
        uasort($clusterStats, fn($a, $b) => $a['avg_score'] <=> $b['avg_score']);

        $labeled = [];
        $labelIndex = 0;
        foreach ($clusterStats as $stat) {
            $labeled[$labelIndex] = $stat['indices'];
            $labelIndex++;
        }

        return $labeled;
    }

    public static function getClusterLabel(int $cluster): string
    {
        if (isset(self::CLUSTER_LABELS[$cluster])) {
            return self::CLUSTER_LABELS[$cluster];
        }

        // Untuk K > 3, cluster di atas 2 dianggap Risiko Tinggi
        return $cluster <= 0 ? 'Risiko Rendah' : 'Risiko Tinggi';
    }

    public static function getClusterColor(int $cluster): string
    {
        return match ($cluster) {
            0 => 'success',
            1 => 'warning',
            2 => 'danger',
            default => $cluster <= 0 ? 'success' : 'danger',
        };
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
