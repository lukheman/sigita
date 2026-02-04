<?php

namespace App\Livewire\Admin;

use App\Models\Balita;
use App\Models\Desa;
use App\Models\Pengukuran;
use App\Models\PeriodeAnalisis;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.admin.livewire-layout')]
#[Title('Dashboard - SIGITA')]
class Dashboard extends Component
{
    public function render()
    {
        // Basic Statistics
        $totalBalita = Balita::count();
        $totalDesa = Desa::count();
        $totalPengukuran = Pengukuran::count();
        $totalPetugas = User::where('role', 'petugas')->count();

        // Gender Distribution
        $balitaLakiLaki = Balita::where('jenis_kelamin', 'L')->count();
        $balitaPerempuan = Balita::where('jenis_kelamin', 'P')->count();

        // Monthly Pengukuran Stats (current month vs last month)
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth();

        $pengukuranBulanIni = Pengukuran::whereMonth('tanggal_ukur', $currentMonth)
            ->whereYear('tanggal_ukur', $currentYear)
            ->count();

        $pengukuranBulanLalu = Pengukuran::whereMonth('tanggal_ukur', $lastMonth->month)
            ->whereYear('tanggal_ukur', $lastMonth->year)
            ->count();

        $pengukuranTrend = $pengukuranBulanLalu > 0
            ? round((($pengukuranBulanIni - $pengukuranBulanLalu) / $pengukuranBulanLalu) * 100, 1)
            : 0;

        // Latest Pengukuran
        $latestPengukuran = Pengukuran::with(['balita.desa'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Balita per Desa
        $balitaPerDesa = Desa::withCount('balita')
            ->orderBy('balita_count', 'desc')
            ->limit(5)
            ->get();

        // Age Distribution (using PHP calculation for SQLite compatibility)
        $allBalita = Balita::all();
        $ageDistribution = [
            '0-12 bulan' => 0,
            '13-24 bulan' => 0,
            '25-36 bulan' => 0,
            '37-48 bulan' => 0,
            '49-60 bulan' => 0,
        ];

        foreach ($allBalita as $balita) {
            $usia = $balita->usiaInBulan();
            if ($usia <= 12) {
                $ageDistribution['0-12 bulan']++;
            } elseif ($usia <= 24) {
                $ageDistribution['13-24 bulan']++;
            } elseif ($usia <= 36) {
                $ageDistribution['25-36 bulan']++;
            } elseif ($usia <= 48) {
                $ageDistribution['37-48 bulan']++;
            } else {
                $ageDistribution['49-60 bulan']++;
            }
        }

        // Latest Analisis (if exists)
        $latestAnalisis = PeriodeAnalisis::with('user')
            ->orderBy('tanggal_proses', 'desc')
            ->first();

        return view('livewire.admin.dashboard', compact(
            'totalBalita',
            'totalDesa',
            'totalPengukuran',
            'totalPetugas',
            'balitaLakiLaki',
            'balitaPerempuan',
            'pengukuranBulanIni',
            'pengukuranTrend',
            'latestPengukuran',
            'balitaPerDesa',
            'ageDistribution',
            'latestAnalisis'
        ));
    }
}
