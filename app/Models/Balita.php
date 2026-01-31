<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Balita extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan.
     */
    protected $table = 'balita';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'desa_id',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'nama_orang_tua',
        'tanggal_lahir',
    ];

    /**
     * Atribut yang harus di-cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    /**
     * Relasi: Balita milik satu Desa.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * Relasi: Balita memiliki banyak Pengukuran.
     */
    public function pengukuran(): HasMany
    {
        return $this->hasMany(Pengukuran::class);
    }

    /**
     * Mendapatkan pengukuran terakhir.
     */
    public function pengukuranTerakhir()
    {
        return $this->pengukuran()->latest('tanggal_ukur')->first();
    }

    /**
     * Menghitung usia dalam bulan dari tanggal lahir.
     */
    public function usiaInBulan(): int
    {
        return Carbon::parse($this->tanggal_lahir)->diffInMonths(Carbon::now());
    }

    /**
     * Menghitung usia dalam format "X tahun Y bulan".
     */
    public function usiaFormatted(): string
    {
        $lahir = Carbon::parse($this->tanggal_lahir);
        $tahun = $lahir->diffInYears(Carbon::now());
        $bulan = $lahir->copy()->addYears($tahun)->diffInMonths(Carbon::now());

        if ($tahun > 0) {
            return "{$tahun} tahun {$bulan} bulan";
        }

        return "{$bulan} bulan";
    }

    /**
     * Mendapatkan label jenis kelamin.
     */
    public function jenisKelaminLabel(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    /**
     * Scope: Filter berdasarkan desa.
     */
    public function scopeByDesa($query, int $desaId)
    {
        return $query->where('desa_id', $desaId);
    }

    /**
     * Scope: Filter berdasarkan jenis kelamin.
     */
    public function scopeByJenisKelamin($query, string $jenisKelamin)
    {
        return $query->where('jenis_kelamin', $jenisKelamin);
    }
}
