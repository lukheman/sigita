<?php

namespace App\Livewire\Admin;

use App\Models\Desa;
use App\Models\PeriodeAnalisis;
use App\Services\KMeansService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.livewire-layout')]
#[Title('Analisis K-Means - SIGITA')]
class AnalisisKMeans extends Component
{
    use WithPagination;

    // Filter untuk riwayat
    #[Url(as: 'q')]
    public string $search = '';

    // Form untuk analisis baru
    public string $judul = '';
    public string $filterBulan = '';
    public string $filterTahun = '';
    public string $filterDesa = '';
    public int $jumlahCluster = 3;

    // State
    public bool $showModal = false;
    public bool $showResultModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public ?PeriodeAnalisis $selectedPeriode = null;
    public bool $isProcessing = false;
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->filterBulan = (string) date('n');
        $this->filterTahun = (string) date('Y');
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->judul = '';
        $this->filterBulan = (string) date('n');
        $this->filterTahun = (string) date('Y');
        $this->filterDesa = '';
        $this->jumlahCluster = 3;
        $this->errorMessage = null;
    }

    public function runAnalysis(): void
    {
        $this->validate([
            'jumlahCluster' => ['required', 'integer', 'min:2', 'max:5'],
            'filterBulan' => ['required', 'integer', 'min:1', 'max:12'],
            'filterTahun' => ['required', 'integer', 'min:2020', 'max:2030'],
        ]);

        $this->isProcessing = true;
        $this->errorMessage = null;

        try {
            $service = new KMeansService($this->jumlahCluster);

            $filters = [
                'bulan' => $this->filterBulan,
                'tahun' => $this->filterTahun,
            ];

            if (!empty($this->filterDesa)) {
                $filters['desa_id'] = $this->filterDesa;
            }

            $periode = $service->runAnalysis($filters, $this->judul);

            session()->flash('success', "Analisis K-Means berhasil! {$periode->total_data} data diproses dalam {$periode->jumlah_cluster} cluster.");

            $this->closeModal();
            $this->selectedPeriode = $periode->load(['hasilCluster.pengukuran.balita.desa', 'user']);
            $this->showResultModal = true;

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    public function viewResult(int $id): void
    {
        $this->selectedPeriode = PeriodeAnalisis::with(['hasilCluster.pengukuran.balita.desa', 'user'])
            ->findOrFail($id);
        $this->showResultModal = true;
    }

    public function closeResultModal(): void
    {
        $this->showResultModal = false;
        $this->selectedPeriode = null;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            PeriodeAnalisis::destroy($this->deletingId);
            session()->flash('success', 'Data analisis berhasil dihapus.');
        }

        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function render()
    {
        $riwayatAnalisis = PeriodeAnalisis::query()
            ->with('user')
            ->when($this->search, function ($query) {
                $query->where('judul', 'like', '%' . $this->search . '%');
            })
            ->orderBy('tanggal_proses', 'desc')
            ->paginate(10);

        $desaOptions = Desa::orderBy('nama_desa')->pluck('nama_desa', 'id')->toArray();

        $bulanOptions = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $currentYear = (int) date('Y');
        $tahunOptions = array_combine(
            range($currentYear - 4, $currentYear),
            range($currentYear - 4, $currentYear)
        );

        return view('livewire.admin.analisis-kmeans', [
            'riwayatAnalisis' => $riwayatAnalisis,
            'desaOptions' => $desaOptions,
            'bulanOptions' => $bulanOptions,
            'tahunOptions' => $tahunOptions,
        ]);
    }
}
