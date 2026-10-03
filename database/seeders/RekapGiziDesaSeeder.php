<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\RekapGiziDesa;
use Illuminate\Database\Seeder;

class RekapGiziDesaSeeder extends Seeder
{
    /**
     * Data awal rekap agregat per desa.
     * Nilai null = data belum tersedia (bukan nol).
     * Periode default: 2026-01.
     */
    public function run(string $periode = '2026-01'): void
    {
        $data = [
            // [nama_desa, stunting, gizi_kurang, bb_kurang, gizi_lebih, gizi_baik]
            ['Lamedai', 18, null, null, null, null],
            ['Lalonggolosua', 9, 5, 12, null, null],
            ['Petudua', 1, 2, 6, null, null],
            ['Pewisoa Jaya', 5, 0, 1, null, null],
            ['Puundaipa', 1, 0, 7, null, null],
            ['Lamoiko', 7, 1, 1, null, null],
            ['Rahanggada', 2, 2, 4, null, null],
            ['Tondowolio', 7, 0, 1, null, null],
            ['Oneeha', 12, 0, 4, null, null],
            ['Anaiwol', 18, 4, 6, null, null],
            ['Palewai', 2, 6, 10, null, null],
            ['Tanggetada', 2, 0, 2, null, null],
            ['Popalia', 4, 3, 3, null, null],
            ['Tinggo', 3, 0, 2, null, null],
        ];

        $count = 0;
        foreach ($data as [$namaDesa, $stunting, $giziKurang, $bbKurang, $giziLebih, $giziBaik]) {
            $desa = Desa::where('nama_desa', $namaDesa)->first();
            if (! $desa) {
                $this->command->warn("Desa '{$namaDesa}' tidak ditemukan, dilewati.");
                continue;
            }

            RekapGiziDesa::updateOrCreate(
                ['desa_id' => $desa->id, 'periode' => $periode],
                [
                    'jumlah_stunting' => $stunting,
                    'jumlah_gizi_kurang' => $giziKurang,
                    'jumlah_bb_kurang' => $bbKurang,
                    'jumlah_gizi_lebih' => $giziLebih,
                    'jumlah_gizi_baik' => $giziBaik,
                    'catatan' => 'Data awal import tabel rekap.',
                ]
            );
            $count++;
        }

        $this->command->info("✓ RekapGiziDesaSeeder: {$count} rekap periode {$periode} berhasil dibuat");
    }
}
