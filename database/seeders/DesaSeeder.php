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
        // Data desa di Kecamatan Tanggetada, Kabupaten Kolaka
        $desaList = [
            ['nama_desa' => 'Tanggetada', 'keterangan' => 'Ibu kota kecamatan'],
            ['nama_desa' => 'Pewisoa Jaya', 'keterangan' => null],
            ['nama_desa' => 'Tondowolio', 'keterangan' => null],
            ['nama_desa' => 'Peoho', 'keterangan' => null],
            ['nama_desa' => 'Lalonggopi', 'keterangan' => null],
            ['nama_desa' => 'Rahanggada', 'keterangan' => null],
            ['nama_desa' => 'Puupi', 'keterangan' => null],
            ['nama_desa' => 'Bou', 'keterangan' => null],
            ['nama_desa' => 'Lamoluo', 'keterangan' => null],
            ['nama_desa' => 'Torobulu', 'keterangan' => null],
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
