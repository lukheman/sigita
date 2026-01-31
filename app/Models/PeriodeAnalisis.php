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
}
