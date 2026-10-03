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
    /** Batas Winsorization IQR per fitur: ['q1'=>, 'q3'=>, 'iqr'=>, 'lower'=>, 'upper'=>] */
    protected array $winsorBounds = [];
    /** Mean/std populasi per fitur untuk Z-score: ['mean'=>[], 'std'=>[]] */
    protected array $meanStd = ['mean' => [], 'std' => []];
    /** Data setelah Winsorization (skala asli count, sejajar $this->data) */
    protected array $winsorizedData = [];
    /** Riwayat iterasi untuk audit detail */
    protected array $iterationsHistory = [];
    protected float $wcssK1 = 0.0;
    protected float $wcssFinal = 0.0;
    /** Centroid awal manual dalam skala Z-score (boleh negatif), null = otomatis KMeans++ */
    protected ?array $customInitialCentroids = null;
    /** Label paksa untuk K=1: 2 = Risiko Rendah, 1 = Risiko Tinggi, null = otomatis */
    protected ?int $fixedLabel = null;
    /** @var string[] Desa yang dilewati karena data belum lengkap */
    protected array $skipped = [];

    // Fitur agregat desa (persentase) — satu titik data = satu desa.
    // Sumber tunggal: RekapGiziDesa::FITUR_KEYS (5 atribut).
    protected array $criteria = [];

    // Bobot skor risiko untuk labelling cluster (total 1.0).
    protected array $riskWeights = [];

    // Label cluster berdasarkan tingkat risiko: 1 = Risiko Tinggi, 2 = Risiko Rendah
    public const CLUSTER_LABELS = [
        1 => 'Risiko Tinggi',
        2 => 'Risiko Rendah',
    ];

    public function __construct(int $k = 2, int $maxIterations = 100)
    {
        if (! in_array($k, [1, 2], true)) {
            throw new \InvalidArgumentException('Jumlah cluster (K) hanya mendukung 1 atau 2.');
        }
        $this->k = $k;
        $this->maxIterations = $maxIterations;
        $this->criteria = RekapGiziDesa::FITUR_KEYS;
        $this->riskWeights = RekapGiziDesa::RISK_WEIGHTS;
    }

    /**
     * Memaksa label tunggal untuk mode K=1.
     * 2 = semua desa Risiko Rendah, 1 = semua desa Risiko Tinggi.
     */
    public function setFixedLabel(?int $label): self
    {
        if ($label !== null && ! in_array($label, [1, 2], true)) {
            throw new \InvalidArgumentException('Label paksa hanya mendukung 1 (Tinggi) atau 2 (Rendah).');
        }
        $this->fixedLabel = $label;

        return $this;
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
     * Menetapkan centroid awal manual dalam skala Z-score (boleh negatif).
     * Contoh: [['jumlah_stunting' => 0.2, 'jumlah_gizi_kurang' => -0.1, ...], [...]]
     * Harus berisi tepat K centroid. Kosongkan (jangan panggil) untuk otomatis KMeans++.
     */
    public function setInitialCentroids(array $centroids): self
    {
        if (count($centroids) !== $this->k) {
            throw new \Exception('Centroid awal harus berisi tepat ' . $this->k . ' centroid.');
        }

        foreach ($centroids as $i => $c) {
            foreach ($this->criteria as $key) {
                if (! isset($c[$key]) || ! is_numeric($c[$key])) {
                    throw new \Exception("Centroid awal C" . ($i + 1) . " belum lengkap: {$key} harus diisi angka (boleh negatif).");
                }
            }
        }

        $rounded = [];
        foreach (array_values($centroids) as $c) {
            $row = [];
            foreach ($this->criteria as $key) {
                $row[$key] = round((float) $c[$key], 4);
            }
            $rounded[] = $row;
        }
        $this->customInitialCentroids = $rounded;

        return $this;
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
                    'alasan' => 'Indikator stunting/gizi kurang/BB kurang/gizi lebih/gizi baik belum lengkap (NULL)',
                ];
                continue;
            }
            $vector = $r->toFeatureVector();
            if ($vector === null) {
                continue;
            }

            $data[] = array_merge([
                'rekap_id' => $r->id,
                'desa_id' => $r->desa_id,
                'desa_nama' => $r->desa->nama_desa ?? 'Unknown',
                'periode' => $r->periode,
                'jumlah_stunting' => $r->jumlah_stunting,
                'jumlah_gizi_kurang' => $r->jumlah_gizi_kurang,
                'jumlah_bb_kurang' => $r->jumlah_bb_kurang,
                'jumlah_gizi_lebih' => $r->jumlah_gizi_lebih,
                'jumlah_gizi_baik' => $r->jumlah_gizi_baik,
                'skor_risiko' => $r->skor_risiko ?? $this->riskScore($vector),
            ], $vector);
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
     * Menjalankan algoritma K-Means clustering.
     * Pipeline sesuai rumus manual: Winsorization IQR -> Z-score populasi -> Euclidean K-Means -> WCSS/Elbow.
     */
    public function performClustering(): array
    {
        [$winsorized, $bounds] = $this->winsorizeDataset($this->data);
        $this->winsorizedData = $winsorized;
        $this->winsorBounds = $bounds;

        [$normalizedData, $meanStd] = $this->normalizeZScore($winsorized);
        $this->meanStd = $meanStd;

        // WCSS untuk K=1 (Elbow baseline): jumlah kuadrat jarak ke rata-rata.
        $this->wcssK1 = $this->computeWcssK1($normalizedData);

        $manual = $this->customInitialCentroids !== null;

        // Mode K=1: satu cluster berisi semua desa (centroid = rata-rata).
        if ($this->k === 1) {
            $label = $this->fixedLabel ?? 2;
            $meanNormalized = [$this->meanCentroid($normalizedData)];

            if ($manual) {
                // Input manual sudah dalam skala Z-score (boleh negatif) — pakai langsung.
                $initialNormalized = [$this->customInitialCentroids[0]];
                $initialOriginal = [$this->denormalizeCentroids($initialNormalized)[0]];
            } else {
                $initialNormalized = $meanNormalized;
                $initialOriginal = $this->denormalizeCentroids($meanNormalized);
            }

            $this->centroids = [$label => $initialNormalized[0]];
            $final = [$label => $meanNormalized[0]];
            $iterations = $this->hasConverged($this->centroids, $final) ? 1 : 2;
            $this->centroids = $final;

            $assign = $this->buildAssignments($normalizedData, $this->centroids);
            $this->wcssFinal = $assign['wcss'];
            $this->iterationsHistory = [[
                'iteration' => 1,
                'centroids_normalized' => $this->centroids,
                'centroids_original' => $this->denormalizeCentroids($this->centroids),
                'assignments' => $assign['per_point'],
                'wcss' => $assign['wcss'],
            ]];

            return [
                'centroids' => $this->denormalizeCentroids($this->centroids),
                'centroids_normalized' => $this->centroids,
                'clusters' => [$label => array_keys($normalizedData)],
                'iterations' => $iterations,
                'data_count' => count($this->data),
                'skipped' => $this->skipped,
                'winsorized_data' => $this->winsorizedData,
                'normalized_data' => $normalizedData,
                'winsor_bounds' => $this->winsorBounds,
                'mean_std' => $this->meanStd,
                'wcss_k1' => $this->wcssK1,
                'wcss_final' => $this->wcssFinal,
                'iterations_history' => $this->iterationsHistory,
                'min_max' => $this->minMax,
                'centroids_initial' => $initialOriginal,
                'centroids_initial_normalized' => $initialNormalized,
                'centroid_manual' => $manual,
            ];
        }

        if ($manual) {
            // Input manual sudah dalam skala Z-score (boleh negatif) — pakai langsung.
            $initialNormalized = $this->customInitialCentroids;
            $initialOriginal = $this->denormalizeCentroids($initialNormalized);
            $this->centroids = $initialNormalized;
        } else {
            $this->centroids = $this->initializeCentroidsKMeansPlusPlus($normalizedData);
            $initialNormalized = $this->centroids;
            $initialOriginal = $this->denormalizeCentroids($initialNormalized);
        }

        $iteration = 0;
        $prevCentroids = [];
        $clusters = [];
        $this->iterationsHistory = [];

        while ($iteration < $this->maxIterations) {
            $prevCentroids = $this->centroids;
            $clusters = array_fill(0, $this->k, []);

            foreach ($normalizedData as $key => $point) {
                $closestCentroidIndex = $this->getClosestCentroid($point);
                $clusters[$closestCentroidIndex][] = $key;
            }

            $this->centroids = $this->updateCentroids($clusters, $normalizedData);

            $assign = $this->buildAssignments($normalizedData, $this->centroids);
            $this->iterationsHistory[] = [
                'iteration' => $iteration + 1,
                'centroids_normalized' => $this->centroids,
                'centroids_original' => $this->denormalizeCentroids($this->centroids),
                'assignments' => $assign['per_point'],
                'wcss' => $assign['wcss'],
            ];

            if ($this->hasConverged($prevCentroids, $this->centroids)) {
                break;
            }

            $iteration++;
        }

        $finalAssign = $this->buildAssignments($normalizedData, $this->centroids);
        $this->wcssFinal = $finalAssign['wcss'];

        // Label cluster berdasarkan skor risiko (bukan nomor mentah K-Means)
        $labeledClusters = $this->labelClusters($clusters);

        return [
            'centroids' => $this->denormalizeCentroids($this->centroids),
            'centroids_normalized' => $this->centroids,
            'clusters' => $labeledClusters,
            'iterations' => $iteration + 1,
            'data_count' => count($this->data),
            'skipped' => $this->skipped,
            'winsorized_data' => $this->winsorizedData,
            'normalized_data' => $normalizedData,
            'winsor_bounds' => $this->winsorBounds,
            'mean_std' => $this->meanStd,
            'wcss_k1' => $this->wcssK1,
            'wcss_final' => $this->wcssFinal,
            'iterations_history' => $this->iterationsHistory,
            'min_max' => $this->minMax,
            'centroids_initial' => $initialOriginal,
            'centroids_initial_normalized' => $initialNormalized,
            'centroid_manual' => $manual,
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

            // Snapshot fitur count mentah agar histori tidak berubah saat rekap diedit
            $snapshot = [];
            foreach ($this->data as $d) {
                $row = [
                    'rekap_id' => $d['rekap_id'],
                    'desa_id' => $d['desa_id'],
                    'desa_nama' => $d['desa_nama'],
                ];
                foreach ($this->criteria as $key) {
                    $row[$key] = $d[$key];
                }
                $row['skor_risiko'] = $d['skor_risiko'];
                $snapshot[] = $row;
            }

            // Data setelah Winsorization (skala asli count)
            $winsorized = [];
            foreach ($result['winsorized_data'] ?? [] as $i => $row) {
                $w = ['desa_nama' => $this->data[$i]['desa_nama'] ?? '-'];
                foreach ($this->criteria as $key) {
                    $w[$key] = round((float) ($row[$key] ?? 0), 4);
                }
                $winsorized[] = $w;
            }

            // Data ternormalisasi Z-score populasi agar tampil di modal hasil
            $normalisasi = [];
            foreach ($result['normalized_data'] ?? [] as $i => $row) {
                $norm = ['desa_nama' => $this->data[$i]['desa_nama'] ?? '-'];
                foreach ($this->criteria as $key) {
                    $norm[$key] = round((float) ($row[$key] ?? 0), 4);
                }
                $normalisasi[] = $norm;
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
                'data_normalisasi' => $normalisasi,
                'data_minmax' => $result['min_max'] ?? null,
                'data_centroid_normalized' => $result['centroids_normalized'] ?? null,
                'data_centroid_initial' => $result['centroids_initial'] ?? null,
                'centroid_manual' => $result['centroid_manual'] ?? false,
                'data_winsor_bounds' => $result['winsor_bounds'] ?? null,
                'data_mean_std' => $result['mean_std'] ?? null,
                'data_winsorized' => $winsorized,
                'data_wcss' => [
                    'k1' => $result['wcss_k1'] ?? 0,
                    'final' => $result['wcss_final'] ?? 0,
                ],
                'data_iterations' => $result['iterations_history'] ?? null,
            ]);

            $normalized = $result['normalized_data'] ?? [];

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

    /**
     * Kuantil linear (Excel PERCENTILE.INC): rank = p*(n-1), interpolasi linear.
     */
    public static function quantileLinear(array $sortedAsc, float $p): float
    {
        $n = count($sortedAsc);
        if ($n === 0) {
            return 0.0;
        }
        if ($n === 1) {
            return (float) $sortedAsc[0];
        }
        $rank = $p * ($n - 1);
        $low = (int) floor($rank);
        $high = (int) ceil($rank);
        if ($low === $high) {
            return (float) $sortedAsc[$low];
        }
        $frac = $rank - $low;

        return (float) ($sortedAsc[$low] + $frac * ($sortedAsc[$high] - $sortedAsc[$low]));
    }

    /**
     * Batas Winsorization IQR untuk satu fitur.
     * IQR = Q3-Q1, Bawah = Q1-1.5*IQR, Atas = Q3+1.5*IQR.
     */
    public static function winsorBoundsForValues(array $values): array
    {
        $sorted = $values;
        sort($sorted, SORT_NUMERIC);
        $q1 = self::quantileLinear($sorted, 0.25);
        $q3 = self::quantileLinear($sorted, 0.75);
        $iqr = $q3 - $q1;

        return [
            'q1' => $q1,
            'q3' => $q3,
            'iqr' => $iqr,
            'lower' => $q1 - 1.5 * $iqr,
            'upper' => $q3 + 1.5 * $iqr,
        ];
    }

    /**
     * Winsorization dataset: clip tiap fitur ke [lower, upper].
     * @return array{0: array, 1: array} [winsorized, boundsPerFitur]
     */
    public function winsorizeDataset(array $data): array
    {
        $bounds = [];
        foreach ($this->criteria as $key) {
            $vals = array_map(fn($d) => (float) ($d[$key] ?? 0), $data);
            $b = self::winsorBoundsForValues($vals);
            $outliers = 0;
            foreach ($vals as $v) {
                if ($v < $b['lower'] || $v > $b['upper']) {
                    $outliers++;
                }
            }
            $b['outliers'] = $outliers;
            $bounds[$key] = $b;
        }

        $winsorized = [];
        foreach ($data as $i => $d) {
            $row = $d;
            foreach ($this->criteria as $key) {
                $v = (float) ($d[$key] ?? 0);
                $row[$key] = min(max($v, $bounds[$key]['lower']), $bounds[$key]['upper']);
            }
            $winsorized[$i] = $row;
        }

        return [$winsorized, $bounds];
    }

    /**
     * Normalisasi Z-score memakai std populasi (dibagi N,equiv STDEV.P).
     * @return array{0: array, 1: array} [normalized, ['mean'=>[], 'std'=>[]]]
     */
    public function normalizeZScore(array $winsorizedData): array
    {
        $n = count($winsorizedData);
        $mean = array_fill_keys($this->criteria, 0.0);
        $std = array_fill_keys($this->criteria, 0.0);

        if ($n === 0) {
            $this->meanStd = ['mean' => $mean, 'std' => $std];
            return [[], $this->meanStd];
        }

        foreach ($winsorizedData as $d) {
            foreach ($this->criteria as $key) {
                $mean[$key] += (float) ($d[$key] ?? 0);
            }
        }
        foreach ($this->criteria as $key) {
            $mean[$key] /= $n;
        }

        foreach ($winsorizedData as $d) {
            foreach ($this->criteria as $key) {
                $diff = ((float) ($d[$key] ?? 0)) - $mean[$key];
                $std[$key] += $diff * $diff;
            }
        }
        foreach ($this->criteria as $key) {
            $std[$key] = $n > 0 ? sqrt($std[$key] / $n) : 0.0;
        }

        $this->meanStd = ['mean' => $mean, 'std' => $std];

        $normalized = [];
        foreach ($winsorizedData as $i => $d) {
            $row = $d;
            foreach ($this->criteria as $key) {
                $s = $std[$key];
                $row[$key] = $s == 0 ? 0.0 : (((float) ($d[$key] ?? 0)) - $mean[$key]) / $s;
            }
            $normalized[$i] = $row;
        }

        return [$normalized, $this->meanStd];
    }

    /**
     * WCSS K=1: jumlah kuadrat jarak tiap titik ke rata-rata ternormalisasi.
     * Setelah Z-score nilainya = 5*N (5 fitur).
     */
    protected function computeWcssK1(array $normalizedData): float
    {
        if (empty($normalizedData)) {
            return 0.0;
        }
        $mean = $this->meanCentroid($normalizedData);
        $total = 0.0;
        foreach ($normalizedData as $point) {
            $dist = $this->euclideanDistance($point, $mean);
            $total += $dist * $dist;
        }

        return $total;
    }

    /**
     * Assign tiap titik ke centroid terdekat + hitung WCSS.
     * @return array{per_point: array, wcss: float}
     */
    protected function buildAssignments(array $normalizedData, array $centroids): array
    {
        $perPoint = [];
        $wcss = 0.0;
        // Simpan centroid sementara agar getClosest + jarak konsisten saat label berubah
        $prev = $this->centroids;
        $this->centroids = array_values($centroids);
        // Petakan kunci asli -> indeks 0..k-1 bila centroid berlabel 1/2
        $keys = array_keys($centroids);
        foreach ($normalizedData as $i => $point) {
            $dists = [];
            foreach ($keys as $pos => $ck) {
                $dists[$ck] = $this->euclideanDistance($point, $centroids[$ck]);
            }
            $bestKey = array_keys($dists, min($dists))[0];
            $min = $dists[$bestKey];
            // Normalisasi ke indeks posisi 0..k-1 untuk histori yang stabil
            $bestPos = array_search($bestKey, $keys, true);
            $perPoint[$i] = [
                'distances' => array_values($dists),
                'cluster_pos' => $bestPos,
                'cluster_key' => $bestKey,
                'min_distance' => $min,
                'wcss' => $min * $min,
            ];
            $wcss += $min * $min;
        }
        $this->centroids = $prev;

        return ['per_point' => $perPoint, 'wcss' => $wcss];
    }

    /**
     * Normalisasi lama Min-Max — dipertahankan untuk kompatibilitas baca,
     * tidak dipakai pipeline baru.
     */
    protected function normalizeData(array $data): array
    {
        $min = array_fill_keys($this->criteria, INF);
        $max = array_fill_keys($this->criteria, -INF);

        foreach ($data as $d) {
            foreach ($this->criteria as $key) {
                if (($d[$key] ?? INF) < $min[$key]) {
                    $min[$key] = $d[$key];
                }
                if (($d[$key] ?? -INF) > $max[$key]) {
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
        // Pipeline baru: Z-score -> asli = z*std + mean.
        if (! empty($this->meanStd['std'])) {
            $denormalized = [];
            foreach ($centroids as $i => $centroid) {
                $denormalized[$i] = [];
                foreach ($this->criteria as $key) {
                    $std = (float) ($this->meanStd['std'][$key] ?? 0);
                    $mean = (float) ($this->meanStd['mean'][$key] ?? 0);
                    $denormalized[$i][$key] = ($centroid[$key] ?? 0) * $std + $mean;
                }
            }

            return $denormalized;
        }

        $denormalized = [];
        foreach ($centroids as $i => $centroid) {
            $denormalized[$i] = [];
            foreach ($this->criteria as $key) {
                $range = ($this->minMax['max'][$key] ?? 0) - ($this->minMax['min'][$key] ?? 0);
                $denormalized[$i][$key] = ($centroid[$key] * $range) + ($this->minMax['min'][$key] ?? 0);
            }
        }

        return $denormalized;
    }

    protected function meanCentroid(array $normalizedData): array
    {
        $mean = array_fill_keys($this->criteria, 0);
        $count = count($normalizedData);

        if ($count === 0) {
            return $mean;
        }

        foreach ($normalizedData as $row) {
            foreach ($this->criteria as $key) {
                $mean[$key] += (float) ($row[$key] ?? 0);
            }
        }

        foreach ($this->criteria as $key) {
            $mean[$key] /= $count;
        }

        return $mean;
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
     * Skor terendah = Risiko Rendah (2), tertinggi = Risiko Tinggi (1).
     * Centroid ikut diurutkan ulang agar kuncinya sesuai label baru.
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

        // Sort ascending — skor rendah = Risiko Rendah (label 2)
        uasort($clusterStats, fn($a, $b) => $a['avg_score'] <=> $b['avg_score']);

        $labeled = [];
        $newCentroids = [];
        $labelIndex = 0;
        foreach ($clusterStats as $rawIndex => $stat) {
            $newLabel = $this->k - $labelIndex;
            $labeled[$newLabel] = $stat['indices'];
            $newCentroids[$newLabel] = $this->centroids[$rawIndex];
            $labelIndex++;
        }
        $this->centroids = $newCentroids;

        return $labeled;
    }

    public static function getClusterLabel(int $cluster): string
    {
        // 0 = penomoran lama untuk Risiko Rendah; selain 1 dan 2 ikut aturan lama
        // (cluster > 2 pada riwayat lama tersimpan sebagai Risiko Tinggi).
        return match (true) {
            $cluster === 1 => 'Risiko Tinggi',
            $cluster === 2 || $cluster === 0 => 'Risiko Rendah',
            default => $cluster <= 0 ? 'Risiko Rendah' : 'Risiko Tinggi',
        };
    }

    public static function getClusterColor(int $cluster): string
    {
        return match (true) {
            $cluster === 1 => 'danger',
            $cluster === 2 || $cluster === 0 => 'success',
            default => $cluster <= 0 ? 'success' : 'danger',
        };
    }

    public static function getKategoriColor(string $kategori): string
    {
        return match ($kategori) {
            'Risiko Rendah' => 'success',
            'Risiko Sedang' => 'warning',
            default => 'danger',
        };
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
