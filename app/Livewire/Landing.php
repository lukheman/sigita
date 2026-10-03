<?php

namespace App\Livewire;

use App\Models\Desa;
use App\Models\RekapGiziDesa;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.guest.layout')]
#[Title('SIGITA - Sistem Informasi Gizi Balita')]
class Landing extends Component
{
    public function render()
    {
        $periode = RekapGiziDesa::query()->orderBy('periode', 'desc')->value('periode');

        $totalStunting = $periode
            ? (int) RekapGiziDesa::byPeriode($periode)->sum('jumlah_stunting')
            : 0;

        return view('livewire.landing', [
            'totalStunting' => $totalStunting,
            'totalDesa' => Desa::count(),
            'periode' => $periode,
        ]);
    }
}
