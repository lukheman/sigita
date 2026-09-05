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
            // [nama_desa, jumlah_balita, ditimbang, stunting, gizi_kurang, bb_kurang]
            ['Lamedai', 80, 78, 18, null, null],
            ['Lalonggolosua', 86, 82, 9, 5, 12],
            ['Petudua', 35, 35, 1, 2, 6],
            ['Pewisoa Jaya', 72, 70, 5, 0, 1],
            ['Puundaipa', 15, 14, 1, 0, 7],
            ['Lamoiko', 20, 19, 7, 1, 1],
            ['Rahanggada', 50, 49, 2, 2, 4],
            ['Tondowolio', 59, 58, 7, 0, 1],
            ['Oneeha', 66, 66, 12, 0, 4],
            ['Anaiwol', 297, 289, 18, 4, 6],
            ['Palewai', 133, 132, 2, 6, 10],
            ['Tanggetada', 122, 119, 2, 0, 2],
            ['Popalia', 147, 142, 4, 3, 3],
            ['Tinggo', 35, 34, 3, 0, 2],
        ];

        $count = 0;
        foreach ($data as [$namaDesa, $balita, $ditimbang, $stunting, $giziKurang, $bbKurang]) {
            $desa = Desa::where('nama_desa', $namaDesa)->first();
            if (! $desa) {
                $this->command->warn("Desa '{$namaDesa}' tidak ditemukan, dilewati.");
                continue;
            }

            RekapGiziDesa::updateOrCreate(
                ['desa_id' => $desa->id, 'periode' => $periode],
                [
                    'jumlah_balita' => $balita,
                    'jumlah_ditimbang' => $ditimbang,
                    'jumlah_stunting' => $stunting,
                    'jumlah_gizi_kurang' => $giziKurang,
                    'jumlah_bb_kurang' => $bbKurang,
                    'catatan' => 'Data awal import tabel rekap.',
                ]
            );
            $count++;
        }

        $this->command->info("✓ RekapGiziDesaSeeder: {$count} rekap periode {$periode} berhasil dibuat");
    }
}
