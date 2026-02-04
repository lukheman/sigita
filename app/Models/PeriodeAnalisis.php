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
        'tanggal_proses',
        'jumlah_cluster',
        'total_data',
        'data_centroid',
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
     * Mendapatkan statistik per desa dari hasil cluster.
     */
    public function getDesaStatistics(): array
    {
        $results = $this->hasilCluster()
            ->with('pengukuran.balita.desa')
            ->get();

        $desaStats = [];

        foreach ($results as $hasil) {
            $desa = $hasil->pengukuran->balita->desa ?? null;
            if (!$desa)
                continue;

            $desaId = $desa->id;
            if (!isset($desaStats[$desaId])) {
                $desaStats[$desaId] = [
                    'desa_id' => $desaId,
                    'nama_desa' => $desa->nama_desa,
                    'total' => 0,
                    'cluster_0' => 0,
                    'cluster_1' => 0,
                    'cluster_2' => 0,
                ];
            }

            $desaStats[$desaId]['total']++;
            $clusterKey = 'cluster_' . $hasil->cluster;
            if (isset($desaStats[$desaId][$clusterKey])) {
                $desaStats[$desaId][$clusterKey]++;
            }
        }

        // Hitung persentase dan sort by problem score
        foreach ($desaStats as &$stat) {
            if ($stat['total'] > 0) {
                $stat['pct_gizi_baik'] = round(($stat['cluster_0'] / $stat['total']) * 100, 1);
                $stat['pct_gizi_kurang'] = round(($stat['cluster_1'] / $stat['total']) * 100, 1);
                $stat['pct_gizi_buruk'] = round(($stat['cluster_2'] / $stat['total']) * 100, 1);
                $stat['problem_score'] = ($stat['cluster_2'] * 2) + $stat['cluster_1'];
            }
        }

        // Sort by problem score descending
        uasort($desaStats, fn($a, $b) => ($b['problem_score'] ?? 0) <=> ($a['problem_score'] ?? 0));

        return array_values($desaStats);
    }
}
