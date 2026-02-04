<?php

namespace App\Imports;

use App\Models\Balita;
use App\Models\Desa;
use App\Models\Pengukuran;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PengukuranImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $successCount = 0;
    public int $errorCount = 0;

    /**
     * Mapping kolom Excel ke nama header yang normalized.
     */
    protected function normalizeHeaders(array $row): array
    {
        $mapping = [
            'no' => 'no',
            'nama_desa' => 'nama_desa',
            'nama_posyandu' => 'nama_posyandu',
            'jk' => 'jk',
            'umur_bln' => 'umur_bln',
            'umur' => 'umur_bln',
            'bbu' => 'bbu',
            'bb_u' => 'bbu',
            'tbu_stunting' => 'tbu',
            'tbu' => 'tbu',
            'tb_u' => 'tbu',
            'bbtb' => 'bbtb',
            'bb_tb' => 'bbtb',
            'asi_eksklusif' => 'asi_eksklusif',
            'akses_air_bersih' => 'akses_air_bersih',
        ];

        $normalized = [];
        foreach ($row as $key => $value) {
            $cleanKey = strtolower(str_replace([' ', '(', ')', '/'], ['_', '', '', ''], $key));
            if (isset($mapping[$cleanKey])) {
                $normalized[$mapping[$cleanKey]] = $value;
            } else {
                $normalized[$cleanKey] = $value;
            }
        }

        return $normalized;
    }

    /**
     * Process the collection of rows.
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because row 1 is header

            try {
                $data = $this->normalizeHeaders($row->toArray());

                // Skip empty rows
                if (empty($data['nama_desa']) && empty($data['jk'])) {
                    continue;
                }

                // Find desa
                $desa = Desa::whereRaw('LOWER(nama_desa) = ?', [strtolower(trim($data['nama_desa'] ?? ''))])->first();

                if (!$desa) {
                    $this->errors[] = "Baris {$rowNumber}: Desa '{$data['nama_desa']}' tidak ditemukan di database.";
                    $this->errorCount++;
                    continue;
                }

                // Parse jenis kelamin
                $jk = strtoupper(trim($data['jk'] ?? ''));
                if (!in_array($jk, ['L', 'P'])) {
                    $this->errors[] = "Baris {$rowNumber}: Jenis kelamin harus 'L' atau 'P'.";
                    $this->errorCount++;
                    continue;
                }

                // Parse usia
                $usia = (int) ($data['umur_bln'] ?? 0);
                if ($usia <= 0 || $usia > 60) {
                    $this->errors[] = "Baris {$rowNumber}: Umur harus antara 1-60 bulan.";
                    $this->errorCount++;
                    continue;
                }

                // Parse ASI Eksklusif
                $asiRaw = strtolower(trim($data['asi_eksklusif'] ?? ''));
                $asiEksklusif = in_array($asiRaw, ['ya', 'yes', '1', 'true']);

                // Parse Akses Air Bersih
                $airRaw = strtolower(trim($data['akses_air_bersih'] ?? ''));
                $aksesAirBersih = in_array($airRaw, ['perpipaan', 'sumur', 'pdam', 'ya', 'yes', '1', 'true']);

                // Calculate tanggal lahir from usia
                $tanggalLahir = Carbon::now()->subMonths($usia)->startOfMonth();

                // Create balita
                $balita = Balita::create([
                    'desa_id' => $desa->id,
                    'nama_lengkap' => 'Balita ' . ($data['nama_posyandu'] ?? 'Import') . ' #' . $rowNumber,
                    'jenis_kelamin' => $jk,
                    'nama_orang_tua' => 'Import dari Excel',
                    'tanggal_lahir' => $tanggalLahir,
                ]);

                // Parse berat badan from status (rough estimate for clustering)
                // Note: This is a simplified approach since actual BB/TB data isn't in the Excel
                $beratBadan = $this->estimateBeratBadan($usia, $jk, $data['bbu'] ?? 'Gizi Baik');
                $tinggiBadan = $this->estimateTinggiBadan($usia, $jk, $data['tbu'] ?? 'Normal');

                // Create pengukuran
                Pengukuran::create([
                    'balita_id' => $balita->id,
                    'tanggal_ukur' => Carbon::now(),
                    'usia_bulan' => $usia,
                    'berat_badan' => $beratBadan,
                    'tinggi_badan' => $tinggiBadan,
                    'asi_eksklusif' => $asiEksklusif,
                    'akses_air_bersih' => $aksesAirBersih,
                    'catatan' => "Status BB/U: " . ($data['bbu'] ?? '-') .
                        ", TB/U: " . ($data['tbu'] ?? '-') .
                        ", BB/TB: " . ($data['bbtb'] ?? '-') .
                        ", Posyandu: " . ($data['nama_posyandu'] ?? '-'),
                ]);

                $this->successCount++;

            } catch (\Exception $e) {
                $this->errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                $this->errorCount++;
            }
        }
    }

    /**
     * Estimate weight based on age and nutrition status.
     * This is a rough estimate based on WHO child growth standards.
     */
    protected function estimateBeratBadan(int $usia, string $jk, string $status): float
    {
        // Base weight by age (simplified WHO median)
        $baseWeight = [
            0 => 3.3,
            6 => 7.5,
            12 => 9.5,
            18 => 10.5,
            24 => 12.0,
            36 => 14.0,
            48 => 16.0,
            60 => 18.0
        ];

        // Interpolate
        $ages = array_keys($baseWeight);
        $weight = $baseWeight[0];

        for ($i = 0; $i < count($ages) - 1; $i++) {
            if ($usia >= $ages[$i] && $usia < $ages[$i + 1]) {
                $ratio = ($usia - $ages[$i]) / ($ages[$i + 1] - $ages[$i]);
                $weight = $baseWeight[$ages[$i]] + $ratio * ($baseWeight[$ages[$i + 1]] - $baseWeight[$ages[$i]]);
                break;
            }
        }

        if ($usia >= 60)
            $weight = $baseWeight[60];

        // Adjust for gender
        if ($jk === 'P')
            $weight *= 0.95;

        // Adjust for status
        $statusLower = strtolower($status);
        if (str_contains($statusLower, 'kurang')) {
            $weight *= 0.85;
        } elseif (str_contains($statusLower, 'buruk')) {
            $weight *= 0.70;
        }

        return round($weight, 2);
    }

    /**
     * Estimate height based on age and stunting status.
     */
    protected function estimateTinggiBadan(int $usia, string $jk, string $status): float
    {
        // Base height by age (simplified WHO median)
        $baseHeight = [
            0 => 49.0,
            6 => 66.0,
            12 => 75.0,
            18 => 81.0,
            24 => 87.0,
            36 => 95.0,
            48 => 102.0,
            60 => 109.0
        ];

        // Interpolate
        $ages = array_keys($baseHeight);
        $height = $baseHeight[0];

        for ($i = 0; $i < count($ages) - 1; $i++) {
            if ($usia >= $ages[$i] && $usia < $ages[$i + 1]) {
                $ratio = ($usia - $ages[$i]) / ($ages[$i + 1] - $ages[$i]);
                $height = $baseHeight[$ages[$i]] + $ratio * ($baseHeight[$ages[$i + 1]] - $baseHeight[$ages[$i]]);
                break;
            }
        }

        if ($usia >= 60)
            $height = $baseHeight[60];

        // Adjust for gender
        if ($jk === 'P')
            $height *= 0.98;

        // Adjust for status
        $statusLower = strtolower($status);
        if (str_contains($statusLower, 'pendek') && !str_contains($statusLower, 'sangat')) {
            $height *= 0.92;
        } elseif (str_contains($statusLower, 'sangat pendek')) {
            $height *= 0.85;
        }

        return round($height, 2);
    }
}
