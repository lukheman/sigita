<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desa extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan.
     */
    protected $table = 'desa';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_desa',
        'keterangan',
    ];

    /**
     * Relasi: Desa memiliki banyak rekap gizi per periode.
     */
    public function rekapGizi(): HasMany
    {
        return $this->hasMany(RekapGiziDesa::class);
    }

    /**
     * Rekap terbaru untuk satu periode tertentu.
     */
    public function rekapPeriode(string $periode): ?RekapGiziDesa
    {
        return $this->rekapGizi()->byPeriode($periode)->first();
    }
}
