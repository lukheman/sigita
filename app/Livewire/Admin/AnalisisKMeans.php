<?php

namespace App\Livewire\Admin;

use App\Models\PeriodeAnalisis;
use App\Models\RekapGiziDesa;
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

    #[Url(as: 'q')]
    public string $search = '';

    // Form untuk analisis baru (agregat desa) — K tetap 2 (Rendah/Tinggi)
    public string $judul = '';
    public string $periode = '';
    public int $jumlahCluster = 2;

    // Centroid awal manual (satuan asli %). Kosong = otomatis KMeans++.
    public bool $centroidManual = false;
    public array $centroidInputs = [];

    // State
    public bool $showModal = false;
    public bool $showResultModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public ?PeriodeAnalisis $selectedPeriode = null;
    public bool $isProcessing = false;
    public ?string $errorMessage = null;
    public array $skippedDesa = [];

    public function mount(): void
    {
        $this->periode = RekapGiziDesa::query()
            ->orderBy('periode', 'desc')
            ->value('periode') ?? date('Y-m');
        $this->centroidInputs = $this->defaultCentroidInputs();
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

    protected function defaultCentroidInputs(): array
    {
        $blank = [
            'cakupan_penimbangan' => null,
            'persentase_stunting' => null,
            'persentase_gizi_kurang' => null,
            'persentase_bb_kurang' => null,
        ];

        return [$blank, $blank];
    }

    protected function resetForm(): void
    {
        $this->judul = '';
        $this->periode = RekapGiziDesa::query()
            ->orderBy('periode', 'desc')
            ->value('periode') ?? date('Y-m');
        $this->jumlahCluster = 2;
        $this->centroidManual = false;
        $this->centroidInputs = $this->defaultCentroidInputs();
        $this->errorMessage = null;
        $this->skippedDesa = [];
    }

    public function runAnalysis(): void
    {
        $rules = [
            'jumlahCluster' => ['required', 'integer', 'in:2'],
            'periode' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'centroidManual' => ['boolean'],
        ];

        if ($this->centroidManual) {
            foreach ([0, 1] as $i) {
                foreach (['cakupan_penimbangan', 'persentase_stunting', 'persentase_gizi_kurang', 'persentase_bb_kurang'] as $key) {
                    $rules["centroidInputs.{$i}.{$key}"] = ['required', 'numeric', 'min:0', 'max:100'];
                }
            }
        }

        $this->validate($rules, [
            'periode.regex' => 'Format periode harus YYYY-MM, misal 2026-01.',
            'centroidInputs.*.*.required' => 'Semua nilai centroid awal wajib diisi saat mode manual aktif.',
        ]);

        $this->isProcessing = true;
        $this->errorMessage = null;
        $this->skippedDesa = [];

        try {
            $service = new KMeansService($this->jumlahCluster);

            if ($this->centroidManual) {
                $service->setInitialCentroids([
                    [
                        'cakupan_penimbangan' => (float) $this->centroidInputs[0]['cakupan_penimbangan'],
                        'persentase_stunting' => (float) $this->centroidInputs[0]['persentase_stunting'],
                        'persentase_gizi_kurang' => (float) $this->centroidInputs[0]['persentase_gizi_kurang'],
                        'persentase_bb_kurang' => (float) $this->centroidInputs[0]['persentase_bb_kurang'],
                    ],
                    [
                        'cakupan_penimbangan' => (float) $this->centroidInputs[1]['cakupan_penimbangan'],
                        'persentase_stunting' => (float) $this->centroidInputs[1]['persentase_stunting'],
                        'persentase_gizi_kurang' => (float) $this->centroidInputs[1]['persentase_gizi_kurang'],
                        'persentase_bb_kurang' => (float) $this->centroidInputs[1]['persentase_bb_kurang'],
                    ],
                ]);
            }

            $periode_analisis = $service->runAnalysis(['periode' => $this->periode], $this->judul);
            $this->skippedDesa = $service->getSkipped();

            session()->flash('success', "Analisis K-Means berhasil! {$periode_analisis->total_data} desa diproses dalam {$periode_analisis->jumlah_cluster} cluster.");

            $this->closeModal();
            $this->selectedPeriode = $periode_analisis->load(['hasilCluster.rekap.desa', 'user']);
            $this->skippedDesa = $service->getSkipped();
            $this->showResultModal = true;
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    public function viewResult(int $id): void
    {
        $this->selectedPeriode = PeriodeAnalisis::with(['hasilCluster.rekap.desa', 'user'])
            ->findOrFail($id);
        $this->skippedDesa = [];
        $this->showResultModal = true;
    }

    public function closeResultModal(): void
    {
        $this->showResultModal = false;
        $this->selectedPeriode = null;
        $this->skippedDesa = [];
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

        $periodeOptions = RekapGiziDesa::query()
            ->distinct()->orderBy('periode', 'desc')->pluck('periode', 'periode')->toArray();

        return view('livewire.admin.analisis-kmeans', [
            'riwayatAnalisis' => $riwayatAnalisis,
            'periodeOptions' => $periodeOptions,
        ]);
    }
}
