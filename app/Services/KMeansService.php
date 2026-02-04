<?php

namespace App\Services;

use App\Models\HasilCluster;
use App\Models\Pengukuran;
use App\Models\PeriodeAnalisis;
use App\Models\Desa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KMeansService
{
    protected array $data = [];
    protected int $k; // Jumlah cluster
    protected int $maxIterations;
    protected array $centroids = [];
    protected array $minMax = [];

    // Kriteria yang digunakan untuk clustering
    protected array $criteria = [
        'jenis_kelamin',   // Encoded: L=0, P=1
        'usia_bulan',
        'berat_badan',
        'tinggi_badan',
        'asi_eksklusif',   // Boolean: 0/1
        'akses_air_bersih' // Boolean: 0/1
    ];

    // Label cluster berdasarkan karakteristik stunting/gizi
    public const CLUSTER_LABELS = [
        0 => 'Gizi Baik',
        1 => 'Gizi Kurang',
        2 => 'Gizi Buruk/Stunting',
    ];

    public function __construct(int $k = 3, int $maxIterations = 100)
    {
        $this->k = $k;
        $this->maxIterations = $maxIterations;
    }

    /**
     * Menjalankan analisis K-Means dari data pengukuran
     */
    public function runAnalysis(array $filters = [], string $judul = ''): PeriodeAnalisis
    {
        // 1. Ambil data pengukuran dari database
        $this->data = $this->fetchPengukuranData($filters);

        if (count($this->data) < $this->k) {
            throw new \Exception("Jumlah data (" . count($this->data) . ") tidak cukup untuk {$this->k} cluster. Minimal {$this->k} data diperlukan.");
        }

        // 2. Jalankan algoritma K-Means
        $result = $this->performClustering();

        // 3. Simpan hasil ke database
        return $this->saveResults($result, $judul, $filters);
    }

    /**
     * Mengambil data pengukuran dari database dengan kriteria lengkap
     */
    protected function fetchPengukuranData(array $filters = []): array
    {
        $query = Pengukuran::query()
            ->with(['balita.desa'])
            ->select(
                'id',
                'balita_id',
                'berat_badan',
                'tinggi_badan',
                'usia_bulan',
                'tanggal_ukur',
                'asi_eksklusif',
                'akses_air_bersih'
            );

        // Filter berdasarkan bulan
        if (!empty($filters['bulan'])) {
            $query->whereMonth('tanggal_ukur', (int) $filters['bulan']);
        }

        // Filter berdasarkan tahun
        if (!empty($filters['tahun'])) {
            $query->whereYear('tanggal_ukur', (int) $filters['tahun']);
        }

        // Filter berdasarkan desa
        if (!empty($filters['desa_id'])) {
            $query->whereHas('balita', function ($q) use ($filters) {
                $q->where('desa_id', (int) $filters['desa_id']);
            });
        }

        $pengukuran = $query->get();

        // Transform ke format array untuk processing
        $data = [];
        foreach ($pengukuran as $p) {
            $data[] = [
                'pengukuran_id' => $p->id,
                'balita_id' => $p->balita_id,
                'desa_id' => $p->balita->desa_id,
                'desa_nama' => $p->balita->desa->nama_desa ?? 'Unknown',
                // Kriteria untuk clustering
                'jenis_kelamin' => $p->balita->jenis_kelamin === 'L' ? 0 : 1, // Encode: L=0, P=1
                'usia_bulan' => (int) $p->usia_bulan,
                'berat_badan' => (float) $p->berat_badan,
                'tinggi_badan' => (float) $p->tinggi_badan,
                'asi_eksklusif' => $p->asi_eksklusif ? 1 : 0,
                'akses_air_bersih' => $p->akses_air_bersih ? 1 : 0,
            ];
        }

        return $data;
    }

    /**
     * Menjalankan algoritma K-Means clustering
     */
    public function performClustering(): array
    {
        // 1. Normalisasi Data
        $normalizedData = $this->normalizeData($this->data);

        // 2. Inisialisasi Centroid menggunakan K-Means++
        $this->centroids = $this->initializeCentroidsKMeansPlusPlus($normalizedData);

        $iteration = 0;
        $prevCentroids = [];
        $clusters = [];

        // 3. Loop Algoritma (Iterasi)
        while ($iteration < $this->maxIterations) {
            $prevCentroids = $this->centroids;
            $clusters = array_fill(0, $this->k, []);

            // A. Assignment Step
            foreach ($normalizedData as $key => $point) {
                $closestCentroidIndex = $this->getClosestCentroid($point);
                $clusters[$closestCentroidIndex][] = $key;
            }

            // B. Update Step
            $this->centroids = $this->updateCentroids($clusters, $normalizedData);

            // C. Convergence Check
            if ($this->hasConverged($prevCentroids, $this->centroids)) {
                break;
            }

            $iteration++;
        }

        // 4. Label cluster berdasarkan karakteristik (TB dan BB ratio)
        $labeledClusters = $this->labelClusters($clusters);

        // 5. Hitung statistik per desa
        $desaStats = $this->calculateDesaStatistics($labeledClusters);

        return [
            'centroids' => $this->denormalizeCentroids($this->centroids),
            'centroids_normalized' => $this->centroids,
            'clusters' => $labeledClusters,
            'iterations' => $iteration + 1,
            'data_count' => count($this->data),
            'desa_stats' => $desaStats,
        ];
    }

    /**
     * Menghitung statistik per desa
     */
    protected function calculateDesaStatistics(array $clusters): array
    {
        $desaStats = [];

        foreach ($clusters as $clusterIndex => $dataIndices) {
            foreach ($dataIndices as $dataIndex) {
                $desaId = $this->data[$dataIndex]['desa_id'];
                $desaNama = $this->data[$dataIndex]['desa_nama'];

                if (!isset($desaStats[$desaId])) {
                    $desaStats[$desaId] = [
                        'desa_id' => $desaId,
                        'nama_desa' => $desaNama,
                        'total' => 0,
                        'cluster_0' => 0, // Gizi Baik
                        'cluster_1' => 0, // Gizi Kurang
                        'cluster_2' => 0, // Gizi Buruk
                    ];
                }

                $desaStats[$desaId]['total']++;
                $desaStats[$desaId]['cluster_' . $clusterIndex]++;
            }
        }

        // Hitung persentase dan urutkan berdasarkan tingkat masalah gizi
        foreach ($desaStats as &$stat) {
            if ($stat['total'] > 0) {
                $stat['pct_gizi_baik'] = round(($stat['cluster_0'] / $stat['total']) * 100, 1);
                $stat['pct_gizi_kurang'] = round(($stat['cluster_1'] / $stat['total']) * 100, 1);
                $stat['pct_gizi_buruk'] = round(($stat['cluster_2'] / $stat['total']) * 100, 1);
                // Score masalah = semakin tinggi gizi buruk + kurang
                $stat['problem_score'] = ($stat['cluster_2'] * 2) + $stat['cluster_1'];
            }
        }

        // Sort by problem score (descending) - desa dengan masalah terbanyak di atas
        uasort($desaStats, fn($a, $b) => $b['problem_score'] <=> $a['problem_score']);

        return array_values($desaStats);
    }

    /**
     * Menyimpan hasil clustering ke database
     */
    protected function saveResults(array $result, string $judul, array $filters): PeriodeAnalisis
    {
        return DB::transaction(function () use ($result, $judul, $filters) {
            // Generate judul otomatis jika kosong
            if (empty($judul)) {
                $bulan = (int) ($filters['bulan'] ?? date('n'));
                $tahun = (int) ($filters['tahun'] ?? date('Y'));
                $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');
                $judul = "Analisis Stunting {$namaBulan} {$tahun}";
            }

            // Simpan periode analisis
            $periode = PeriodeAnalisis::create([
                'user_id' => Auth::id(),
                'judul' => $judul,
                'tanggal_proses' => now(),
                'jumlah_cluster' => $this->k,
                'total_data' => $result['data_count'],
                'data_centroid' => $result['centroids'],
            ]);

            // Simpan hasil cluster untuk setiap data
            foreach ($result['clusters'] as $clusterIndex => $dataIndices) {
                foreach ($dataIndices as $dataIndex) {
                    $originalData = $this->data[$dataIndex];

                    HasilCluster::create([
                        'periode_analisis_id' => $periode->id,
                        'pengukuran_id' => $originalData['pengukuran_id'],
                        'cluster' => $clusterIndex,
                        'jarak_centroid' => $this->calculateDistanceToCluster(
                            $dataIndex,
                            $clusterIndex,
                            $this->normalizeData($this->data)
                        ),
                    ]);
                }
            }

            return $periode;
        });
    }

    /**
     * Menghitung jarak ke centroid cluster
     */
    protected function calculateDistanceToCluster(int $dataIndex, int $clusterIndex, array $normalizedData): float
    {
        return $this->euclideanDistance(
            $normalizedData[$dataIndex],
            $this->centroids[$clusterIndex]
        );
    }

    /**
     * Menghitung Jarak Euclidean untuk semua kriteria
     */
    protected function euclideanDistance(array $point1, array $point2): float
    {
        $sum = 0;
        foreach ($this->criteria as $metric) {
            $sum += pow(($point1[$metric] - $point2[$metric]), 2);
        }
        return sqrt($sum);
    }

    /**
     * Mencari centroid terdekat untuk satu titik data
     */
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

    /**
     * Menghitung posisi centroid baru (Rata-rata/Mean)
     */
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

    /**
     * Cek apakah algoritma sudah konvergen
     */
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

    /**
     * Normalisasi Min-Max Scaling (0 - 1)
     */
    protected function normalizeData(array $data): array
    {
        // Inisialisasi min max
        $min = array_fill_keys($this->criteria, INF);
        $max = array_fill_keys($this->criteria, -INF);

        foreach ($data as $d) {
            foreach ($this->criteria as $key) {
                if ($d[$key] < $min[$key])
                    $min[$key] = $d[$key];
                if ($d[$key] > $max[$key])
                    $max[$key] = $d[$key];
            }
        }

        $this->minMax = ['min' => $min, 'max' => $max];

        // Terapkan normalisasi
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

    /**
     * Denormalisasi centroid ke nilai asli
     */
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

    /**
     * Inisialisasi centroid menggunakan K-Means++
     */
    protected function initializeCentroidsKMeansPlusPlus(array $data): array
    {
        $centroids = [];

        // Pilih centroid pertama secara acak
        $firstIndex = array_rand($data);
        $centroids[0] = [];
        foreach ($this->criteria as $key) {
            $centroids[0][$key] = $data[$firstIndex][$key];
        }

        // Pilih centroid berikutnya berdasarkan jarak
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

            $random = mt_rand() / mt_getrandmax() * $totalDistance;
            $cumulative = 0;

            foreach ($distances as $key => $dist) {
                $cumulative += $dist;
                if ($cumulative >= $random) {
                    $centroids[$c] = [];
                    foreach ($this->criteria as $k) {
                        $centroids[$c][$k] = $data[$key][$k];
                    }
                    break;
                }
            }
        }

        return $centroids;
    }

    /**
     * Melabeli cluster berdasarkan karakteristik TB dan BB
     * Cluster dengan rata-rata TB+BB terendah = Gizi Buruk
     * Cluster dengan rata-rata TB+BB tertinggi = Gizi Baik
     */
    protected function labelClusters(array $clusters): array
    {
        $clusterStats = [];

        foreach ($clusters as $clusterIndex => $dataIndices) {
            $totalScore = 0;
            $count = count($dataIndices);

            foreach ($dataIndices as $index) {
                // Score = kombinasi TB dan BB (nilai lebih tinggi = lebih baik)
                $totalScore += $this->data[$index]['tinggi_badan'] + $this->data[$index]['berat_badan'];
            }

            $clusterStats[$clusterIndex] = [
                'avg_score' => $count > 0 ? $totalScore / $count : 0,
                'indices' => $dataIndices,
            ];
        }

        // Sort berdasarkan score (descending) - score tinggi = gizi baik
        uasort($clusterStats, fn($a, $b) => $b['avg_score'] <=> $a['avg_score']);

        $labeled = [];
        $labelIndex = 0;
        foreach ($clusterStats as $stat) {
            $labeled[$labelIndex] = $stat['indices'];
            $labelIndex++;
        }

        return $labeled;
    }

    /**
     * Mendapatkan label cluster
     */
    public static function getClusterLabel(int $cluster): string
    {
        return self::CLUSTER_LABELS[$cluster] ?? 'Unknown';
    }

    /**
     * Mendapatkan warna badge untuk cluster
     */
    public static function getClusterColor(int $cluster): string
    {
        return match ($cluster) {
            0 => 'success',  // Gizi Baik
            1 => 'warning',  // Gizi Kurang
            2 => 'danger',   // Gizi Buruk
            default => 'info',
        };
    }

    /**
     * Mendapatkan kriteria yang digunakan
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
