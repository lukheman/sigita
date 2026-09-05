<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekapGiziDesa extends Model
{
    use HasFactory;

    protected $table = 'rekap_gizi_desa';

    protected $fillable = [
        'desa_id',
        'periode',
        'jumlah_balita',
        'jumlah_ditimbang',
        'jumlah_stunting',
        'jumlah_gizi_kurang',
        'jumlah_bb_kurang',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_balita' => 'integer',
            'jumlah_ditimbang' => 'integer',
            'jumlah_stunting' => 'integer',
            'jumlah_gizi_kurang' => 'integer',
            'jumlah_bb_kurang' => 'integer',
        ];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasilCluster(): HasMany
    {
        return $this->hasMany(HasilCluster::class, 'rekap_gizi_desa_id');
    }

    /**
     * Cakupan penimbangan (%) = ditimbang / balita * 100
     */
    public function getCakupanAttribute(): ?float
    {
        if ($this->jumlah_balita <= 0) {
            return null;
        }

        return round(($this->jumlah_ditimbang / $this->jumlah_balita) * 100, 1);
    }

    /**
     * Persentase indikator terhadap jumlah ditimbang.
     * NULL jika pembilang NULL atau penyebut 0 (data belum lengkap).
     */
    public function getPctStuntingAttribute(): ?float
    {
        return $this->pctOf($this->jumlah_stunting);
    }

    public function getPctGiziKurangAttribute(): ?float
    {
        return $this->pctOf($this->jumlah_gizi_kurang);
    }

    public function getPctBbKurangAttribute(): ?float
    {
        return $this->pctOf($this->jumlah_bb_kurang);
    }

    protected function pctOf(?int $pembilang): ?float
    {
        if ($pembilang === null || $this->jumlah_ditimbang <= 0) {
            return null;
        }

        return round(($pembilang / $this->jumlah_ditimbang) * 100, 1);
    }

    /**
     * Data dianggap lengkap jika ketiga indikator terisi (tidak NULL).
     */
    public function isLengkap(): bool
    {
        return $this->jumlah_stunting !== null
            && $this->jumlah_gizi_kurang !== null
            && $this->jumlah_bb_kurang !== null;
    }

    /**
     * Skor risiko tertimbang untuk labelling cluster.
     * Bobot default: stunting 0.5, gizi kurang 0.3, BB kurang 0.2.
     * Mengembalikan null jika ada indikator NULL.
     */
    public function getSkorRisikoAttribute(): ?float
    {
        if (
            $this->pct_stunting === null
            || $this->pct_gizi_kurang === null
            || $this->pct_bb_kurang === null
        ) {
            return null;
        }

        return round(
            ($this->pct_stunting * 0.5)
            + ($this->pct_gizi_kurang * 0.3)
            + ($this->pct_bb_kurang * 0.2),
            2
        );
    }

    /**
     * Fitur vektor untuk clustering (persentase).
     * Mengembalikan null jika tidak lengkap.
     */
    public function toFeatureVector(): ?array
    {
        if (! $this->isLengkap()) {
            return null;
        }

        return [
            'cakupan_penimbangan' => (float) ($this->cakupan ?? 0),
            'persentase_stunting' => (float) $this->pct_stunting,
            'persentase_gizi_kurang' => (float) $this->pct_gizi_kurang,
            'persentase_bb_kurang' => (float) $this->pct_bb_kurang,
        ];
    }

    /**
     * Nama bulan singkat Indonesia untuk label periode.
     */
    public const BULAN_SINGKAT = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /**
     * Format periode YYYY-MM menjadi label "Jan 2026".
     * Format penyimpanan tetap YYYY-MM; hanya tampilan yang diubah.
     */
    public static function formatPeriode(?string $periode): string
    {
        if (empty($periode)) {
            return '-';
        }
        if (! preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $periode, $m)) {
            return $periode;
        }

        return self::BULAN_SINGKAT[(int) $m[2]] . ' ' . $m[1];
    }

    public function getPeriodeLabelAttribute(): string
    {
        return self::formatPeriode($this->periode);
    }

    public function scopeByPeriode($query, string $periode)
    {
        return $query->where('periode', $periode);
    }

    public function scopeLengkap($query)
    {
        return $query->whereNotNull('jumlah_stunting')
            ->whereNotNull('jumlah_gizi_kurang')
            ->whereNotNull('jumlah_bb_kurang');
    }
}
