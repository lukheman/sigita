<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilCluster extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan.
     */
    protected $table = 'hasil_cluster';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'periode_analisis_id',
        'pengukuran_id',
        'cluster',
        'kategori',
    ];

    /**
     * Konstanta untuk kategori stunting.
     */
    public const KATEGORI_SANGAT_PENDEK = 'Sangat Pendek';
    public const KATEGORI_PENDEK = 'Pendek';
    public const KATEGORI_NORMAL = 'Normal';
    public const KATEGORI_TINGGI = 'Tinggi';

    /**
     * Warna untuk setiap kategori (untuk UI).
     */
    public const KATEGORI_COLORS = [
        self::KATEGORI_SANGAT_PENDEK => 'red',
        self::KATEGORI_PENDEK => 'orange',
        self::KATEGORI_NORMAL => 'green',
        self::KATEGORI_TINGGI => 'blue',
    ];

    /**
     * Relasi: HasilCluster milik satu PeriodeAnalisis.
     */
    public function periodeAnalisis(): BelongsTo
    {
        return $this->belongsTo(PeriodeAnalisis::class);
    }

    /**
     * Relasi: HasilCluster milik satu Pengukuran.
     */
    public function pengukuran(): BelongsTo
    {
        return $this->belongsTo(Pengukuran::class);
    }

    /**
     * Mendapatkan data balita melalui pengukuran.
     */
    public function getBalitaAttribute()
    {
        return $this->pengukuran?->balita;
    }

    /**
     * Mendapatkan warna berdasarkan kategori.
     */
    public function getWarna(): string
    {
        return self::KATEGORI_COLORS[$this->kategori] ?? 'gray';
    }

    /**
     * Mengecek apakah hasil menunjukkan stunting (Sangat Pendek atau Pendek).
     */
    public function isStunting(): bool
    {
        return in_array($this->kategori, [
            self::KATEGORI_SANGAT_PENDEK,
            self::KATEGORI_PENDEK,
        ]);
    }

    /**
     * Mengecek apakah hasil menunjukkan normal.
     */
    public function isNormal(): bool
    {
        return $this->kategori === self::KATEGORI_NORMAL;
    }

    /**
     * Scope: Filter hasil berdasarkan kategori.
     */
    public function scopeByKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope: Filter hasil yang stunting.
     */
    public function scopeStunting($query)
    {
        return $query->whereIn('kategori', [
            self::KATEGORI_SANGAT_PENDEK,
            self::KATEGORI_PENDEK,
        ]);
    }

    /**
     * Scope: Filter hasil yang normal.
     */
    public function scopeNormal($query)
    {
        return $query->where('kategori', self::KATEGORI_NORMAL);
    }

    /**
     * Mendapatkan semua kategori yang tersedia.
     */
    public static function getAllKategori(): array
    {
        return [
            self::KATEGORI_SANGAT_PENDEK,
            self::KATEGORI_PENDEK,
            self::KATEGORI_NORMAL,
            self::KATEGORI_TINGGI,
        ];
    }
}
