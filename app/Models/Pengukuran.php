<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengukuran extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan.
     */
    protected $table = 'pengukuran';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'balita_id',
        'tanggal_ukur',
        'usia_bulan',
        'berat_badan',
        'tinggi_badan',
        'catatan',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_ukur' => 'date',
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
        ];
    }

    /**
     * Relasi: Pengukuran milik satu Balita.
     */
    public function balita(): BelongsTo
    {
        return $this->belongsTo(Balita::class);
    }

    /**
     * Relasi: Pengukuran memiliki banyak HasilCluster.
     */
    public function hasilCluster(): HasMany
    {
        return $this->hasMany(HasilCluster::class);
    }

    /**
     * Menghitung Z-Score TB/U (Tinggi Badan per Usia).
     * Ini adalah placeholder - implementasi sebenarnya memerlukan data standar WHO.
     */
    public function hitungZScoreTBU(): ?float
    {
        // TODO: Implementasi perhitungan Z-Score berdasarkan standar WHO
        return null;
    }

    /**
     * Menghitung Z-Score BB/U (Berat Badan per Usia).
     * Ini adalah placeholder - implementasi sebenarnya memerlukan data standar WHO.
     */
    public function hitungZScoreBBU(): ?float
    {
        // TODO: Implementasi perhitungan Z-Score berdasarkan standar WHO
        return null;
    }

    /**
     * Menghitung Z-Score BB/TB (Berat Badan per Tinggi Badan).
     * Ini adalah placeholder - implementasi sebenarnya memerlukan data standar WHO.
     */
    public function hitungZScoreBBTB(): ?float
    {
        // TODO: Implementasi perhitungan Z-Score berdasarkan standar WHO
        return null;
    }

    /**
     * Scope: Filter pengukuran berdasarkan periode (bulan & tahun).
     */
    public function scopeByPeriode($query, int $bulan, int $tahun)
    {
        return $query->whereMonth('tanggal_ukur', $bulan)
            ->whereYear('tanggal_ukur', $tahun);
    }

    /**
     * Scope: Filter pengukuran berdasarkan rentang usia.
     */
    public function scopeByRentangUsia($query, int $minBulan, int $maxBulan)
    {
        return $query->whereBetween('usia_bulan', [$minBulan, $maxBulan]);
    }
}
