<?php

namespace App\Livewire;

use App\Models\Balita;
use App\Models\Desa;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.guest.layout')]
#[Title('SIGITA - Sistem Informasi Gizi Balita')]
class Landing extends Component
{
    public function render()
    {
        return view('livewire.landing', [
            'totalBalita' => Balita::count(),
            'totalDesa' => Desa::count(),
        ]);
    }
}

