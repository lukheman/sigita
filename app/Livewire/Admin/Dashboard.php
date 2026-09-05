<?php

namespace App\Livewire\Admin;

use App\Models\Desa;
use App\Models\PeriodeAnalisis;
use App\Models\RekapGiziDesa;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.admin.livewire-layout')]
#[Title('Dashboard - SIGITA')]
class Dashboard extends Component
{
    public function render()
    {
        $periode = RekapGiziDesa::query()->orderBy('periode', 'desc')->value('periode') ?? date('Y-m');

        $rekapQuery = RekapGiziDesa::byPeriode($periode);

        $totalBalita = (clone $rekapQuery)->sum('jumlah_balita');
        $totalDitimbang = (clone $rekapQuery)->sum('jumlah_ditimbang');
        $totalStunting = (clone $rekapQuery)->sum('jumlah_stunting');
        $totalGiziKurang = (clone $rekapQuery)->sum('jumlah_gizi_kurang');
        $totalBbKurang = (clone $rekapQuery)->sum('jumlah_bb_kurang');
        $totalDesa = Desa::count();
        $totalPetugas = User::where('role', 'petugas')->count();

        $cakupan = $totalBalita > 0 ? round(($totalDitimbang / $totalBalita) * 100, 1) : 0;
        $pctStunting = $totalDitimbang > 0 ? round(($totalStunting / $totalDitimbang) * 100, 1) : 0;

        $belumLengkap = (clone $rekapQuery)
            ->where(fn($q) => $q->whereNull('jumlah_stunting')->orWhereNull('jumlah_gizi_kurang')->orWhereNull('jumlah_bb_kurang'))
            ->with('desa')
            ->get();

        // Top desa berdasarkan jumlah stunting
        $topStunting = (clone $rekapQuery)->with('desa')->orderBy('jumlah_stunting', 'desc')->limit(5)->get();

        // Rekap terbaru per desa pada periode aktif
        $latestRekap = (clone $rekapQuery)->with('desa')->orderBy('jumlah_stunting', 'desc')->limit(5)->get();

        $periodeOptions = RekapGiziDesa::query()
            ->distinct()->orderBy('periode', 'desc')->pluck('periode', 'periode')->toArray();

        // Latest Analisis (if exists)
        $latestAnalisis = PeriodeAnalisis::with('user')
            ->orderBy('tanggal_proses', 'desc')
            ->first();

        return view('livewire.admin.dashboard', compact(
            'periode',
            'periodeOptions',
            'totalBalita',
            'totalDitimbang',
            'totalStunting',
            'totalGiziKurang',
            'totalBbKurang',
            'totalDesa',
            'totalPetugas',
            'cakupan',
            'pctStunting',
            'belumLengkap',
            'topStunting',
            'latestRekap',
            'latestAnalisis'
        ));
    }
}
