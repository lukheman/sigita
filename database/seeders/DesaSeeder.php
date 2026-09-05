<?php

namespace Database\Seeders;

use App\Models\Desa;
use Illuminate\Database\Seeder;

class DesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 14 desa aktif — data rekap agregat (Kec. Tanggetada & sekitarnya)
        $desaList = [
            ['nama_desa' => 'Lamedai', 'keterangan' => null],
            ['nama_desa' => 'Lalonggolosua', 'keterangan' => null],
            ['nama_desa' => 'Petudua', 'keterangan' => null],
            ['nama_desa' => 'Pewisoa Jaya', 'keterangan' => null],
            ['nama_desa' => 'Puundaipa', 'keterangan' => null],
            ['nama_desa' => 'Lamoiko', 'keterangan' => null],
            ['nama_desa' => 'Rahanggada', 'keterangan' => null],
            ['nama_desa' => 'Tondowolio', 'keterangan' => null],
            ['nama_desa' => 'Oneeha', 'keterangan' => null],
            ['nama_desa' => 'Anaiwol', 'keterangan' => null],
            ['nama_desa' => 'Palewai', 'keterangan' => null],
            ['nama_desa' => 'Tanggetada', 'keterangan' => 'Ibu kota kecamatan'],
            ['nama_desa' => 'Popalia', 'keterangan' => null],
            ['nama_desa' => 'Tinggo', 'keterangan' => null],
        ];

        foreach ($desaList as $desa) {
            Desa::updateOrCreate(
                ['nama_desa' => $desa['nama_desa']],
                $desa
            );
        }

        $this->command->info('✓ DesaSeeder: ' . count($desaList) . ' desa berhasil dibuat');
    }
}
