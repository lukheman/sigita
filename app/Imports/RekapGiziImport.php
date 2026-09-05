<?php

namespace App\Imports;

use App\Models\Desa;
use App\Models\RekapGiziDesa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RekapGiziImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $successCount = 0;
    public int $errorCount = 0;
    public string $periodeDefault;

    public function __construct(string $periodeDefault = '')
    {
        $this->periodeDefault = $periodeDefault;
    }

    protected function toIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        // Kosongkan tanda '-' atau 'belum ada' menjadi NULL
        $v = strtolower(trim((string) $value));
        if (in_array($v, ['-', 'na', 'n/a', 'tidak ada data', 'kosong', 'belum ada'])) {
            return null;
        }

        return is_numeric($v) ? (int) $v : null;
    }

    protected function normalizePeriode(mixed $value): string
    {
        $value = trim((string) ($value ?: $this->periodeDefault));
        // Terima YYYY-MM, YYYY/MM, MM-YYYY, atau YYYYMM
        $value = str_replace('/', '-', $value);
        if (preg_match('/^\d{4}-\d{1,2}$/', $value)) {
            [$y, $m] = explode('-', $value);

            return sprintf('%04d-%02d', (int) $y, (int) $m);
        }
        if (preg_match('/^\d{1,2}-\d{4}$/', $value)) {
            [$m, $y] = explode('-', $value);

            return sprintf('%04d-%02d', (int) $y, (int) $m);
        }
        if (preg_match('/^\d{6}$/', $value)) {
            return substr($value, 0, 4) . '-' . substr($value, 4, 2);
        }

        return $this->periodeDefault;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $arr = [];
                foreach ($row->toArray() as $key => $value) {
                    $clean = strtolower(str_replace([' ', '-', '(', ')', '/', '.'], ['_', '_', '', '', '', ''], (string) $key));
                    $clean = preg_replace('/_+/', '_', trim($clean, '_'));
                    $arr[$clean] = is_string($value) ? trim($value) : $value;
                }

                $namaDesa = $arr['desa'] ?? $arr['nama_desa'] ?? null;
                if (empty($namaDesa)) {
                    continue;
                }

                $desa = Desa::whereRaw('LOWER(nama_desa) = ?', [strtolower(trim($namaDesa))])->first();
                if (! $desa) {
                    $this->errors[] = "Baris {$rowNumber}: Desa '{$namaDesa}' tidak ditemukan di database.";
                    $this->errorCount++;
                    continue;
                }

                $periode = $this->normalizePeriode($arr['periode'] ?? $this->periodeDefault);
                if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
                    $this->errors[] = "Baris {$rowNumber}: Periode '{$periode}' tidak valid (format YYYY-MM).";
                    $this->errorCount++;
                    continue;
                }

                // Kolom template: DESA | JUMLAH BALITA | BALITA DI TIMBANG | STUNTING | GIZI KURANG | BB KURANG
                // Periode diambil dari kolom PERIODE jika ada, jika tidak memakai periode default dari modal import.
                $balita = $this->toIntOrNull($arr['jumlah_balita'] ?? $arr['jml_balita'] ?? $arr['balita'] ?? null);
                $ditimbang = $this->toIntOrNull($arr['balita_di_timbang'] ?? $arr['balita_ditimbang'] ?? $arr['jumlah_ditimbang'] ?? $arr['ditimbang'] ?? $arr['jml_ditimbang'] ?? null);
                $stunting = $this->toIntOrNull($arr['stunting'] ?? $arr['jumlah_stunting'] ?? null);
                $giziKurang = $this->toIntOrNull($arr['gizi_kurang'] ?? $arr['jumlah_gizi_kurang'] ?? null);
                $bbKurang = $this->toIntOrNull($arr['bb_kurang'] ?? $arr['jumlah_bb_kurang'] ?? null);

                if ($balita === null || $ditimbang === null) {
                    $this->errors[] = "Baris {$rowNumber}: Kolom JUMLAH BALITA & BALITA DI TIMBANG wajib diisi.";
                    $this->errorCount++;
                    continue;
                }
                if ($balita < 0 || $ditimbang < 0) {
                    $this->errors[] = "Baris {$rowNumber}: Jumlah tidak boleh negatif.";
                    $this->errorCount++;
                    continue;
                }
                if ($ditimbang > $balita) {
                    $this->errors[] = "Baris {$rowNumber}: Ditimbang ({$ditimbang}) melebihi jumlah balita ({$balita}).";
                    $this->errorCount++;
                    continue;
                }
                foreach (['stunting' => $stunting, 'gizi kurang' => $giziKurang, 'BB kurang' => $bbKurang] as $label => $val) {
                    if ($val !== null && ($val < 0 || $val > $ditimbang)) {
                        $this->errors[] = "Baris {$rowNumber}: {$label} harus 0..{$ditimbang}.";
                        $this->errorCount++;
                        continue 2;
                    }
                }

                RekapGiziDesa::updateOrCreate(
                    ['desa_id' => $desa->id, 'periode' => $periode],
                    [
                        'jumlah_balita' => $balita,
                        'jumlah_ditimbang' => $ditimbang,
                        'jumlah_stunting' => $stunting,
                        'jumlah_gizi_kurang' => $giziKurang,
                        'jumlah_bb_kurang' => $bbKurang,
                        'catatan' => $arr['catatan'] ?? null,
                        'created_by' => Auth::id(),
                    ]
                );
                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNumber}: {$e->getMessage()}";
                $this->errorCount++;
            }
        }
    }
}
