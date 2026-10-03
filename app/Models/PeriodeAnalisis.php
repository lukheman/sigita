<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeAnalisis extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan.
     */
    protected $table = 'periode_analisis';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'judul',
        'periode_data',
        'tanggal_proses',
        'jumlah_cluster',
        'total_data',
        'data_centroid',
        'data_snapshot',
        'data_normalisasi',
        'data_minmax',
        'data_centroid_normalized',
        'data_centroid_initial',
        'centroid_manual',
        'data_winsor_bounds',
        'data_mean_std',
        'data_winsorized',
        'data_wcss',
        'data_iterations',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_proses' => 'date',
            'data_centroid' => 'array',
            'data_snapshot' => 'array',
            'data_normalisasi' => 'array',
            'data_minmax' => 'array',
            'data_centroid_normalized' => 'array',
            'data_centroid_initial' => 'array',
            'centroid_manual' => 'boolean',
            'data_winsor_bounds' => 'array',
            'data_mean_std' => 'array',
            'data_winsorized' => 'array',
            'data_wcss' => 'array',
            'data_iterations' => 'array',
        ];
    }

    /**
     * Relasi: PeriodeAnalisis milik satu User (yang memproses).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: PeriodeAnalisis memiliki banyak HasilCluster.
     */
    public function hasilCluster(): HasMany
    {
        return $this->hasMany(HasilCluster::class);
    }

    /**
     * Mendapatkan centroid sebagai array.
     */
    public function getCentroids(): array
    {
        return $this->data_centroid ?? [];
    }

    /**
     * Kunci fitur yang dinormalisasi (Z-score) sebelum K-Means — pipeline baru.
     * Sumber tunggal: RekapGiziDesa::FITUR_KEYS (5 count).
     */
    public static function fiturNormalisasi(): array
    {
        return RekapGiziDesa::FITUR_KEYS;
    }

    /**
     * Kunci fitur efektif untuk analisis ini (baru count vs lama persentase).
     */
    public function resolveFeatureKeys(): array
    {
        $snap = $this->data_snapshot ?? [];
        if (! empty($snap) && is_array($snap[0] ?? null) && array_key_exists('jumlah_stunting', $snap[0])) {
            return RekapGiziDesa::FITUR_KEYS;
        }
        if (! empty($snap) && is_array($snap[0] ?? null) && array_key_exists('persentase_stunting', $snap[0])) {
            return RekapGiziDesa::LEGACY_FITUR_KEYS;
        }
        // Analisis sangat lama tanpa snapshot: pakai kunci baru.
        return RekapGiziDesa::FITUR_KEYS;
    }

    public function resolveFeatureLabels(): array
    {
        return $this->resolveFeatureKeys() === RekapGiziDesa::LEGACY_FITUR_KEYS
            ? RekapGiziDesa::LEGACY_FITUR_LABELS
            : RekapGiziDesa::FITUR_LABELS;
    }

    /**
     * True bila analisis memakai pipeline baru (Winsor + Z-score populasi).
     */
    public function isZScore(): bool
    {
        return ! empty($this->data_mean_std['mean']) || ! empty($this->data_winsor_bounds);
    }

    /**
     * Min-Max tiap fitur. Pakai kolom tersimpan bila ada,
     * hitung ulang dari data_snapshot untuk analisis lama.
     * Pipeline baru (Z-score) tidak memakai Min-Max: kembalikan [] bila isZScore().
     */
    public function getMinMax(): array
    {
        if ($this->isZScore()) {
            return $this->data_minmax ?? [];
        }
        if (! empty($this->data_minmax['min']) && ! empty($this->data_minmax['max'])) {
            return $this->data_minmax;
        }

        $features = $this->resolveFeatureKeys();
        $min = array_fill_keys($features, INF);
        $max = array_fill_keys($features, -INF);

        foreach (($this->data_snapshot ?? []) as $row) {
            foreach ($features as $key) {
                if (! isset($row[$key]) || ! is_numeric($row[$key])) {
                    continue;
                }
                $val = (float) $row[$key];
                if ($val < $min[$key]) {
                    $min[$key] = $val;
                }
                if ($val > $max[$key]) {
                    $max[$key] = $val;
                }
            }
        }

        foreach ($features as $key) {
            if ($min[$key] === INF) {
                $min[$key] = 0;
            }
            if ($max[$key] === -INF) {
                $max[$key] = 0;
            }
        }

        return ['min' => $min, 'max' => $max];
    }

    /**
     * Data ternormalisasi per desa. Pipeline baru = Z-score populasi,
     * pipeline lama = Min-Max 0–1. Pakai kolom tersimpan bila ada,
     * hitung ulang dari data_snapshot untuk analisis lama.
     */
    public function getDataNormalisasi(): array
    {
        if (! empty($this->data_normalisasi) && is_array($this->data_normalisasi)) {
            return $this->data_normalisasi;
        }

        // Fallback lama (Min-Max) untuk analisis tanpa kolom tersimpan.
        $features = $this->resolveFeatureKeys();
        $minMax = $this->getMinMax();
        $result = [];

        foreach (($this->data_snapshot ?? []) as $row) {
            $norm = [
                'desa_nama' => $row['desa_nama'] ?? '-',
            ];
            foreach ($features as $key) {
                $divisor = ($minMax['max'][$key] ?? 0) - ($minMax['min'][$key] ?? 0);
                $val = (float) ($row[$key] ?? 0);
                $norm[$key] = $divisor == 0 ? 0 : round(($val - $minMax['min'][$key]) / $divisor, 4);
            }
            $result[] = $norm;
        }

        return $result;
    }

    /**
     * Centroid ternormalisasi. Pipeline baru = Z-score, lama = 0–1.
     * Pakai kolom tersimpan bila ada, turunkan dari data_centroid untuk analisis lama.
     */
    public function getCentroidsNormalized(): array
    {
        if (! empty($this->data_centroid_normalized) && is_array($this->data_centroid_normalized)) {
            return $this->data_centroid_normalized;
        }

        $minMax = $this->getMinMax();
        $features = $this->resolveFeatureKeys();
        $result = [];

        foreach (($this->data_centroid ?? []) as $centroid) {
            $norm = [];
            foreach ($features as $key) {
                $divisor = ($minMax['max'][$key] ?? 0) - ($minMax['min'][$key] ?? 0);
                $val = (float) ($centroid[$key] ?? 0);
                $norm[$key] = $divisor == 0 ? 0 : round(($val - $minMax['min'][$key]) / $divisor, 4);
            }
            $result[] = $norm;
        }

        return $result;
    }

    /**
     * Centroid awal yang dipakai saat analisis.
     * Pipeline baru mode manual: skala Z-score (boleh negatif).
     * Pipeline baru otomatis & lama: satuan asli (count / %).
     * Kosong untuk analisis lama yang belum menyimpan kolom ini.
     */
    public function getCentroidsInitial(): array
    {
        if (! empty($this->data_centroid_initial) && is_array($this->data_centroid_initial)) {
            return $this->data_centroid_initial;
        }

        return [];
    }

    /**
     * Batas Winsorization IQR per fitur (pipeline baru). [] untuk analisis lama.
     */
    public function getWinsorBounds(): array
    {
        return $this->data_winsor_bounds ?? [];
    }

    /**
     * Mean/std populasi per fitur (pipeline baru). [] untuk analisis lama.
     */
    public function getMeanStd(): array
    {
        return $this->data_mean_std ?? [];
    }

    /**
     * Data setelah Winsorization per desa (pipeline baru).
     */
    public function getDataWinsorized(): array
    {
        return $this->data_winsorized ?? [];
    }

    /**
     * WCSS: ['k1'=>, 'final'=>] (pipeline baru).
     */
    public function getWcss(): array
    {
        return $this->data_wcss ?? [];
    }

    /**
     * Riwayat iterasi K-Means (pipeline baru).
     */
    public function getIterationsHistory(): array
    {
        return $this->data_iterations ?? [];
    }

    /**
     * Label periode data, misal "Jan 2026".
     */
    public function getPeriodeLabelAttribute(): string
    {
        return RekapGiziDesa::formatPeriode($this->periode_data);
    }

    /**
     * Mendapatkan jumlah data per cluster.
     */
    public function getDistribusiCluster(): array
    {
        return $this->hasilCluster()
            ->selectRaw('cluster, COUNT(*) as total')
            ->groupBy('cluster')
            ->orderBy('cluster')
            ->pluck('total', 'cluster')
            ->toArray();
    }

    /**
     * Mendapatkan persentase per cluster.
     */
    public function getPersentaseCluster(): array
    {
        $distribusi = $this->getDistribusiCluster();
        $total = array_sum($distribusi);

        if ($total === 0) {
            return [];
        }

        return array_map(fn($count) => round(($count / $total) * 100, 2), $distribusi);
    }

    /**
     * Scope: Filter periode berdasarkan tahun.
     */
    public function scopeByTahun($query, int $tahun)
    {
        return $query->whereYear('tanggal_proses', $tahun);
    }

    /**
     * Scope: Urutkan dari yang terbaru.
     */
    public function scopeTerbaru($query)
    {
        return $query->orderBy('tanggal_proses', 'desc');
    }

    /**
     * Mendapatkan statistik per desa dari hasil cluster agregat.
     * Setiap hasil = satu desa (bukan satu balita).
     */
    public function getDesaStatistics(): array
    {
        $results = $this->hasilCluster()
            ->with('rekap.desa')
            ->get();

        $desaStats = [];

        foreach ($results as $hasil) {
            $rekap = $hasil->rekap;
            $desa = $rekap?->desa;
            if (! $desa || ! $rekap) {
                continue;
            }

            // Dahulukan kategori tersimpan agar riwayat lama (penomoran 0/1/2)
            // tetap tampil benar; fallback ke penomoran baru (1 = Tinggi, 2 = Rendah).
            $label = $hasil->kategori;
            if (! in_array($label, ['Risiko Rendah', 'Risiko Sedang', 'Risiko Tinggi'], true)) {
                $label = match (true) {
                    (int) $hasil->cluster === 1 => 'Risiko Tinggi',
                    (int) $hasil->cluster === 2 || (int) $hasil->cluster === 0 => 'Risiko Rendah',
                    default => ((int) $hasil->cluster <= 0 ? 'Risiko Rendah' : 'Risiko Tinggi'),
                };
            }

            $kategori = match ($label) {
                'Risiko Rendah' => ['label' => 'Risiko Rendah', 'variant' => 'success', 'icon' => '🟢', 'keterangan' => 'Indikator gizi relatif baik dibanding desa lain'],
                'Risiko Sedang' => ['label' => 'Risiko Sedang', 'variant' => 'warning', 'icon' => '🟡', 'keterangan' => 'Indikator gizi perlu perhatian'],
                default => ['label' => 'Risiko Tinggi', 'variant' => 'danger', 'icon' => '🔴', 'keterangan' => 'Prioritas intervensi gizi'],
            };

            $desaStats[] = [
                'desa_id' => $desa->id,
                'nama_desa' => $desa->nama_desa,
                'rekap_id' => $rekap->id,
                'periode' => $rekap->periode,
                'jumlah_stunting' => $rekap->jumlah_stunting,
                'jumlah_gizi_kurang' => $rekap->jumlah_gizi_kurang,
                'jumlah_bb_kurang' => $rekap->jumlah_bb_kurang,
                'jumlah_gizi_lebih' => $rekap->jumlah_gizi_lebih,
                'jumlah_gizi_baik' => $rekap->jumlah_gizi_baik,
                'cluster' => $hasil->cluster,
                'kategori' => $label,
                'kategori_desa' => $kategori['label'],
                'kategori_variant' => $kategori['variant'],
                'kategori_icon' => $kategori['icon'],
                'kategori_keterangan' => $kategori['keterangan'],
                'jarak_centroid' => $hasil->jarak_centroid,
                'skor_risiko' => $hasil->skor_risiko ?? $rekap->skor_risiko,
                'problem_score' => $hasil->skor_risiko ?? $rekap->skor_risiko ?? 0,
            ];
        }

        // Sort by skor risiko descending — desa paling bermasalah di atas
        usort($desaStats, fn($a, $b) => ($b['problem_score'] ?? 0) <=> ($a['problem_score'] ?? 0));

        return array_values($desaStats);
    }
}
