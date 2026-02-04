<?php

namespace Database\Seeders;

use App\Models\Balita;
use App\Models\Desa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BalitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $desaIds = Desa::pluck('id')->toArray();

        if (empty($desaIds)) {
            $this->command->error('✗ BalitaSeeder: Tidak ada data desa. Jalankan DesaSeeder terlebih dahulu.');
            return;
        }

        // Data sample balita
        $balitaList = [
            // Desa 1
            ['nama_lengkap' => 'Muhammad Aldi', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Ahmad Sudirman', 'tanggal_lahir' => '2023-03-15'],
            ['nama_lengkap' => 'Siti Aisyah', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Budi Santoso', 'tanggal_lahir' => '2022-07-20'],
            ['nama_lengkap' => 'Rizky Pratama', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Hendra Wijaya', 'tanggal_lahir' => '2023-01-10'],

            // Desa 2
            ['nama_lengkap' => 'Nurul Hidayah', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Dedi Kurniawan', 'tanggal_lahir' => '2022-11-05'],
            ['nama_lengkap' => 'Ahmad Fauzi', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Eko Prasetyo', 'tanggal_lahir' => '2023-05-25'],
            ['nama_lengkap' => 'Dewi Anggraini', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Fajar Nugroho', 'tanggal_lahir' => '2022-09-12'],

            // Desa 3
            ['nama_lengkap' => 'Budi Setiawan', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Gunawan Wibowo', 'tanggal_lahir' => '2023-02-28'],
            ['nama_lengkap' => 'Rina Marlina', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Hadi Susanto', 'tanggal_lahir' => '2022-06-18'],
            ['nama_lengkap' => 'Dimas Ardiansyah', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Irfan Hakim', 'tanggal_lahir' => '2023-08-08'],

            // Desa 4
            ['nama_lengkap' => 'Putri Ramadhani', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Joko Widodo', 'tanggal_lahir' => '2022-12-01'],
            ['nama_lengkap' => 'Farhan Maulana', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Kurniawan Adi', 'tanggal_lahir' => '2023-04-14'],
            ['nama_lengkap' => 'Anisa Safitri', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Lukman Hakim', 'tanggal_lahir' => '2022-08-22'],

            // Desa 5
            ['nama_lengkap' => 'Rafif Hidayat', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Mulyadi Rahman', 'tanggal_lahir' => '2023-06-30'],
            ['nama_lengkap' => 'Zahra Amelia', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Nasrul Amin', 'tanggal_lahir' => '2022-10-17'],
            ['nama_lengkap' => 'Galih Permana', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Oki Setiawan', 'tanggal_lahir' => '2023-07-09'],

            // Desa 6-10 (tambahan untuk variasi)
            ['nama_lengkap' => 'Intan Permatasari', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Purnomo Jati', 'tanggal_lahir' => '2022-05-03'],
            ['nama_lengkap' => 'Bayu Aditya', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Qomar Zain', 'tanggal_lahir' => '2023-09-21'],
            ['nama_lengkap' => 'Melati Kusuma', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Rudi Hartono', 'tanggal_lahir' => '2022-04-11'],
            ['nama_lengkap' => 'Yoga Pratama', 'jenis_kelamin' => 'L', 'nama_orang_tua' => 'Surya Andi', 'tanggal_lahir' => '2023-10-05'],
            ['nama_lengkap' => 'Fitri Handayani', 'jenis_kelamin' => 'P', 'nama_orang_tua' => 'Taufik Ismail', 'tanggal_lahir' => '2022-02-14'],
        ];

        $desaCount = count($desaIds);
        foreach ($balitaList as $index => $balita) {
            // Distribusi balita ke desa secara merata
            $desaId = $desaIds[$index % $desaCount];

            Balita::updateOrCreate(
                array_merge($balita, [
                    'desa_id' => $desaId,
                    'tanggal_lahir' => Carbon::parse($balita['tanggal_lahir']),
                ])
            );
        }

        $this->command->info('✓ BalitaSeeder: ' . count($balitaList) . ' balita berhasil dibuat');
    }
}
