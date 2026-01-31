<?php

namespace Database\Seeders;

use App\Models\Balita;
use App\Models\Pengukuran;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PengukuranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $balitaList = Balita::all();

        if ($balitaList->isEmpty()) {
            $this->command->error('✗ PengukuranSeeder: Tidak ada data balita. Jalankan BalitaSeeder terlebih dahulu.');
            return;
        }

        $totalPengukuran = 0;

        foreach ($balitaList as $balita) {
            // Hitung usia balita saat ini dalam bulan
            $usiaSekarang = Carbon::parse($balita->tanggal_lahir)->diffInMonths(Carbon::now());

            // Buat data pengukuran untuk beberapa bulan terakhir (3-6 bulan terakhir)
            $jumlahPengukuran = min(rand(3, 6), $usiaSekarang); // Maksimal sesuai usia

            for ($i = 0; $i < $jumlahPengukuran; $i++) {
                $tanggalUkur = Carbon::now()->subMonths($i);
                $usiaSaatUkur = $usiaSekarang - $i;

                if ($usiaSaatUkur < 0)
                    continue;

                // Generate data pengukuran yang realistis berdasarkan usia
                $pengukuranData = $this->generatePengukuranData($balita, $usiaSaatUkur);

                Pengukuran::updateOrCreate(
                    [
                        'balita_id' => $balita->id,
                        'tanggal_ukur' => $tanggalUkur->startOfMonth(),
                    ],
                    [
                        'usia_bulan' => $usiaSaatUkur,
                        'berat_badan' => $pengukuranData['berat_badan'],
                        'tinggi_badan' => $pengukuranData['tinggi_badan'],
                        'catatan' => $pengukuranData['catatan'],
                    ]
                );

                $totalPengukuran++;
            }
        }

        $this->command->info('✓ PengukuranSeeder: ' . $totalPengukuran . ' data pengukuran berhasil dibuat');
    }

    /**
     * Generate data pengukuran yang realistis berdasarkan usia dan jenis kelamin.
     * Data ini mengacu pada standar pertumbuhan WHO.
     */
    private function generatePengukuranData(Balita $balita, int $usiaBulan): array
    {
        $jenisKelamin = $balita->jenis_kelamin;

        // Standar median TB (cm) dan BB (kg) berdasarkan usia (simplified)
        // Data ini adalah approximasi untuk demo
        $standarTB = $this->getStandarTB($usiaBulan, $jenisKelamin);
        $standarBB = $this->getStandarBB($usiaBulan, $jenisKelamin);

        // Variasi: Normal (70%), Pendek (20%), Sangat Pendek (10%)
        $variasi = rand(1, 100);

        if ($variasi <= 10) {
            // Sangat Pendek (< -3 SD)
            $faktorTB = rand(85, 90) / 100; // 85-90% dari standar
            $faktorBB = rand(75, 85) / 100;
            $catatan = 'Perlu perhatian khusus';
        } elseif ($variasi <= 30) {
            // Pendek (-3 SD sampai -2 SD)
            $faktorTB = rand(90, 95) / 100; // 90-95% dari standar
            $faktorBB = rand(85, 92) / 100;
            $catatan = 'Perlu pemantauan';
        } else {
            // Normal
            $faktorTB = rand(95, 105) / 100; // 95-105% dari standar
            $faktorBB = rand(92, 108) / 100;
            $catatan = null;
        }

        return [
            'berat_badan' => round($standarBB * $faktorBB, 2),
            'tinggi_badan' => round($standarTB * $faktorTB, 2),
            'catatan' => $catatan,
        ];
    }

    /**
     * Get standar tinggi badan berdasarkan usia dan jenis kelamin (median WHO).
     */
    private function getStandarTB(int $usiaBulan, string $jenisKelamin): float
    {
        // Simplified approximation - untuk data real, gunakan tabel WHO lengkap
        $baseline = $jenisKelamin === 'L' ? 50.0 : 49.0; // TB lahir
        $growthRate = $jenisKelamin === 'L' ? 2.5 : 2.4; // cm per bulan (menurun seiring usia)

        // Growth rate menurun seiring bertambahnya usia
        $tb = $baseline;
        for ($i = 1; $i <= $usiaBulan; $i++) {
            if ($i <= 12) {
                $tb += $growthRate;
            } elseif ($i <= 24) {
                $tb += $growthRate * 0.5;
            } else {
                $tb += $growthRate * 0.35;
            }
        }

        return round($tb, 2);
    }

    /**
     * Get standar berat badan berdasarkan usia dan jenis kelamin (median WHO).
     */
    private function getStandarBB(int $usiaBulan, string $jenisKelamin): float
    {
        // Simplified approximation - untuk data real, gunakan tabel WHO lengkap
        $baseline = $jenisKelamin === 'L' ? 3.3 : 3.2; // BB lahir
        $growthRate = $jenisKelamin === 'L' ? 0.7 : 0.65; // kg per bulan (menurun seiring usia)

        $bb = $baseline;
        for ($i = 1; $i <= $usiaBulan; $i++) {
            if ($i <= 6) {
                $bb += $growthRate;
            } elseif ($i <= 12) {
                $bb += $growthRate * 0.5;
            } elseif ($i <= 24) {
                $bb += $growthRate * 0.25;
            } else {
                $bb += $growthRate * 0.15;
            }
        }

        return round($bb, 2);
    }
}
