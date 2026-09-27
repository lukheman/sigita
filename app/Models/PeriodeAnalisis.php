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
     * Kunci fitur yang dinormalisasi (Min-Max) sebelum K-Means.
     * Sumber tunggal: RekapGiziDesa::FITUR_KEYS (5 atribut).
     */
    public static function fiturNormalisasi(): array
    {
        return RekapGiziDesa::FITUR_KEYS;
    }

    /**
     * Min-Max tiap fitur. Pakai kolom tersimpan bila ada,
     * hitung ulang dari data_snapshot untuk analisis lama.
     */
    public function getMinMax(): array
    {
        if (! empty($this->data_minmax['min']) && ! empty($this->data_minmax['max'])) {
            return $this->data_minmax;
        }

        $features = self::fiturNormalisasi();
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
     * Data ternormalisasi per desa (0–1). Pakai kolom tersimpan bila ada,
     * hitung ulang dari data_snapshot untuk analisis lama.
     */
    public function getDataNormalisasi(): array
    {
        if (! empty($this->data_normalisasi) && is_array($this->data_normalisasi)) {
            return $this->data_normalisasi;
        }

        $minMax = $this->getMinMax();
        $features = self::fiturNormalisasi();
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
     * Centroid ternormalisasi (0–1). Pakai kolom tersimpan bila ada,
     * turunkan dari data_centroid + min-max untuk analisis lama.
     */
    public function getCentroidsNormalized(): array
    {
        if (! empty($this->data_centroid_normalized) && is_array($this->data_centroid_normalized)) {
            return $this->data_centroid_normalized;
        }

        $minMax = $this->getMinMax();
        $features = self::fiturNormalisasi();
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
     * Centroid awal yang dipakai saat analisis (satuan asli %).
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
                'jumlah_balita' => $rekap->jumlah_balita,
                'jumlah_ditimbang' => $rekap->jumlah_ditimbang,
                'cakupan' => $rekap->cakupan,
                'jumlah_stunting' => $rekap->jumlah_stunting,
                'jumlah_gizi_kurang' => $rekap->jumlah_gizi_kurang,
                'jumlah_bb_kurang' => $rekap->jumlah_bb_kurang,
                'jumlah_gizi_lebih' => $rekap->jumlah_gizi_lebih,
                'jumlah_gizi_baik' => $rekap->jumlah_gizi_baik,
                'pct_stunting' => $rekap->pct_stunting,
                'pct_gizi_kurang' => $rekap->pct_gizi_kurang,
                'pct_bb_kurang' => $rekap->pct_bb_kurang,
                'pct_gizi_lebih' => $rekap->pct_gizi_lebih,
                'pct_gizi_baik' => $rekap->pct_gizi_baik,
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
