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

            $kategori = match ($hasil->cluster) {
                0 => ['label' => 'Risiko Rendah', 'variant' => 'success', 'icon' => '🟢', 'keterangan' => 'Indikator gizi relatif baik dibanding desa lain'],
                1 => ['label' => 'Risiko Sedang', 'variant' => 'warning', 'icon' => '🟡', 'keterangan' => 'Indikator gizi perlu perhatian'],
                default => ['label' => 'Risiko Tinggi', 'variant' => 'danger', 'icon' => '🔴', 'keterangan' => 'Prioritas intervensi gizi'],
            };
            // Untuk K > 3, cluster di atas 2 dianggap Risiko Tinggi
            if ($hasil->cluster > 2) {
                $kategori = ['label' => 'Risiko Tinggi', 'variant' => 'danger', 'icon' => '🔴', 'keterangan' => 'Prioritas intervensi gizi'];
            }

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
                'pct_stunting' => $rekap->pct_stunting,
                'pct_gizi_kurang' => $rekap->pct_gizi_kurang,
                'pct_bb_kurang' => $rekap->pct_bb_kurang,
                'cluster' => $hasil->cluster,
                'kategori' => $hasil->kategori ?? $kategori['label'],
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
