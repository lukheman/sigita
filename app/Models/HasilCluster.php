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
        'rekap_gizi_desa_id',
        'cluster',
        'kategori',
        'jarak_centroid',
        'skor_risiko',
    ];

    protected function casts(): array
    {
        return [
            'cluster' => 'integer',
            'jarak_centroid' => 'float',
            'skor_risiko' => 'float',
        ];
    }

    /**
     * Label risiko desa hasil clustering agregat.
     */
    public const KATEGORI_RENDAH = 'Risiko Rendah';
    public const KATEGORI_SEDANG = 'Risiko Sedang';
    public const KATEGORI_TINGGI = 'Risiko Tinggi';

    // Alias kompatibilitas dengan kode lama
    public const KATEGORI_SANGAT_PENDEK = self::KATEGORI_TINGGI;
    public const KATEGORI_PENDEK = self::KATEGORI_SEDANG;
    public const KATEGORI_NORMAL = self::KATEGORI_RENDAH;

    /**
     * Warna untuk setiap kategori (untuk UI).
     */
    public const KATEGORI_COLORS = [
        self::KATEGORI_RENDAH => 'green',
        self::KATEGORI_SEDANG => 'orange',
        self::KATEGORI_TINGGI => 'red',
    ];

    /**
     * Relasi: HasilCluster milik satu PeriodeAnalisis.
     */
    public function periodeAnalisis(): BelongsTo
    {
        return $this->belongsTo(PeriodeAnalisis::class);
    }

    /**
     * Relasi: HasilCluster milik satu RekapGiziDesa.
     */
    public function rekap(): BelongsTo
    {
        return $this->belongsTo(RekapGiziDesa::class, 'rekap_gizi_desa_id');
    }

    /**
     * Mendapatkan data desa melalui rekap.
     */
    public function getDesaAttribute()
    {
        return $this->rekap?->desa;
    }

    /**
     * Mendapatkan warna berdasarkan kategori.
     */
    public function getWarna(): string
    {
        return self::KATEGORI_COLORS[$this->kategori] ?? 'gray';
    }

    public function isRisikoTinggi(): bool
    {
        return $this->kategori === self::KATEGORI_TINGGI;
    }

    public function isRisikoRendah(): bool
    {
        return $this->kategori === self::KATEGORI_RENDAH;
    }

    // Alias kompatibilitas
    public function isStunting(): bool
    {
        return $this->isRisikoTinggi();
    }

    public function isNormal(): bool
    {
        return $this->isRisikoRendah();
    }

    /**
     * Scope: Filter hasil berdasarkan kategori.
     */
    public function scopeByKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeRisikoTinggi($query)
    {
        return $query->where('kategori', self::KATEGORI_TINGGI);
    }

    public function scopeRisikoRendah($query)
    {
        return $query->where('kategori', self::KATEGORI_RENDAH);
    }

    // Alias kompatibilitas
    public function scopeStunting($query)
    {
        return $query->risikoTinggi();
    }

    public function scopeNormal($query)
    {
        return $query->risikoRendah();
    }

    /**
     * Mendapatkan semua kategori yang tersedia.
     */
    public static function getAllKategori(): array
    {
        return [
            self::KATEGORI_RENDAH,
            self::KATEGORI_SEDANG,
            self::KATEGORI_TINGGI,
        ];
    }
}
