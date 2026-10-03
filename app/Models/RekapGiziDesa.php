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
        'jumlah_stunting',
        'jumlah_gizi_kurang',
        'jumlah_bb_kurang',
        'jumlah_gizi_lebih',
        'jumlah_gizi_baik',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_stunting' => 'integer',
            'jumlah_gizi_kurang' => 'integer',
            'jumlah_bb_kurang' => 'integer',
            'jumlah_gizi_lebih' => 'integer',
            'jumlah_gizi_baik' => 'integer',
        ];
    }

    /**
     * Kunci fitur count mentah untuk clustering (5 atribut).
     * Sesuai rumus manual: STUNTING, GIZI KURANG, BB-KURANG, GIZI LEBIH, GIZI BAIK
     * memakai jumlah jiwa (bukan persentase).
     */
    public const FITUR_KEYS = [
        'jumlah_stunting',
        'jumlah_gizi_kurang',
        'jumlah_bb_kurang',
        'jumlah_gizi_lebih',
        'jumlah_gizi_baik',
    ];

    /**
     * Label tampil tiap fitur (count).
     */
    public const FITUR_LABELS = [
        'jumlah_stunting' => 'Stunting',
        'jumlah_gizi_kurang' => 'Gizi Kurang',
        'jumlah_bb_kurang' => 'BB Kurang',
        'jumlah_gizi_lebih' => 'Gizi Lebih',
        'jumlah_gizi_baik' => 'Gizi Baik',
    ];

    /**
     * Kunci fitur lama (persentase) — hanya untuk membaca riwayat analisis lama.
     */
    public const LEGACY_FITUR_KEYS = [
        'persentase_stunting',
        'persentase_gizi_kurang',
        'persentase_bb_kurang',
        'persentase_gizi_lebih',
        'persentase_gizi_baik',
    ];

    public const LEGACY_FITUR_LABELS = [
        'persentase_stunting' => 'Stunting (%)',
        'persentase_gizi_kurang' => 'Gizi Kurang (%)',
        'persentase_bb_kurang' => 'BB Kurang (%)',
        'persentase_gizi_lebih' => 'Gizi Lebih (%)',
        'persentase_gizi_baik' => 'Gizi Baik (%)',
    ];

    /**
     * Kolom jumlah sumber tiap fitur (untuk mode count, pemetaan identitas).
     */
    public const FITUR_JUMLAH = [
        'jumlah_stunting' => 'jumlah_stunting',
        'jumlah_gizi_kurang' => 'jumlah_gizi_kurang',
        'jumlah_bb_kurang' => 'jumlah_bb_kurang',
        'jumlah_gizi_lebih' => 'jumlah_gizi_lebih',
        'jumlah_gizi_baik' => 'jumlah_gizi_baik',
    ];

    /**
     * Bobot skor risiko untuk labelling cluster (total 1.0).
     * Diterapkan pada count mentah.
     */
    public const RISK_WEIGHTS = [
        'jumlah_stunting' => 0.4,
        'jumlah_gizi_kurang' => 0.25,
        'jumlah_bb_kurang' => 0.15,
        'jumlah_gizi_lebih' => 0.1,
        'jumlah_gizi_baik' => 0.1,
    ];

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
     * Data dianggap lengkap jika kelima indikator terisi (tidak NULL).
     */
    public function isLengkap(): bool
    {
        return $this->jumlah_stunting !== null
            && $this->jumlah_gizi_kurang !== null
            && $this->jumlah_bb_kurang !== null
            && $this->jumlah_gizi_lebih !== null
            && $this->jumlah_gizi_baik !== null;
    }

    /**
     * Skor risiko tertimbang untuk labelling cluster (dari count mentah).
     * Mengembalikan null jika ada indikator NULL.
     */
    public function getSkorRisikoAttribute(): ?float
    {
        $score = 0;

        foreach (self::RISK_WEIGHTS as $key => $w) {
            $val = $this->{$key};
            if ($val === null) {
                return null;
            }
            $score += ((float) $val) * $w;
        }

        return round($score, 2);
    }

    /**
     * Fitur vektor untuk clustering (count mentah).
     * Mengembalikan null jika tidak lengkap.
     */
    public function toFeatureVector(): ?array
    {
        if (! $this->isLengkap()) {
            return null;
        }

        $vector = [];
        foreach (self::FITUR_KEYS as $key) {
            $vector[$key] = (float) $this->{$key};
        }

        return $vector;
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
            ->whereNotNull('jumlah_bb_kurang')
            ->whereNotNull('jumlah_gizi_lebih')
            ->whereNotNull('jumlah_gizi_baik');
    }
}
