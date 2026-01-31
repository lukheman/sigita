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
     * Relasi: Desa memiliki banyak Balita.
     */
    public function balita(): HasMany
    {
        return $this->hasMany(Balita::class);
    }

    /**
     * Mendapatkan jumlah balita di desa ini.
     */
    public function jumlahBalita(): int
    {
        return $this->balita()->count();
    }
}
