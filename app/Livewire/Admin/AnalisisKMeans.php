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

    // Mode analisis: 1 cluster Rendah saja, 1 cluster Tinggi saja, atau 2 cluster.
    public const MODE_OPTIONS = [
        'rendah' => ['k' => 1, 'label' => 2, 'nama' => '1 Cluster (Risiko Rendah)'],
        'tinggi' => ['k' => 1, 'label' => 1, 'nama' => '1 Cluster (Risiko Tinggi)'],
        '2cluster' => ['k' => 2, 'label' => null, 'nama' => '2 Cluster (Rendah / Tinggi)'],
    ];

    // Form untuk analisis baru (agregat desa)
    public string $judul = '';
    public string $periode = '';
    public string $modeAnalisis = '2cluster';

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
        $blank = array_fill_keys(RekapGiziDesa::FITUR_KEYS, null);

        return [$blank, $blank];
    }

    protected function resetForm(): void
    {
        $this->judul = '';
        $this->periode = RekapGiziDesa::query()
            ->orderBy('periode', 'desc')
            ->value('periode') ?? date('Y-m');
        $this->modeAnalisis = '2cluster';
        $this->centroidManual = false;
        $this->centroidInputs = $this->defaultCentroidInputs();
        $this->errorMessage = null;
        $this->skippedDesa = [];
    }

    public function modeK(): int
    {
        return self::MODE_OPTIONS[$this->modeAnalisis]['k'] ?? 2;
    }

    public function modeFixedLabel(): ?int
    {
        return self::MODE_OPTIONS[$this->modeAnalisis]['label'] ?? null;
    }

    public function runAnalysis(): void
    {
        $rules = [
            'modeAnalisis' => ['required', 'in:rendah,tinggi,2cluster'],
            'periode' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'centroidManual' => ['boolean'],
        ];

        $k = self::MODE_OPTIONS[$this->modeAnalisis]['k'] ?? 2;

        if ($this->centroidManual) {
            for ($i = 0; $i < $k; $i++) {
                foreach (RekapGiziDesa::FITUR_KEYS as $key) {
                    $rules["centroidInputs.{$i}.{$key}"] = ['required', 'numeric'];
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
            $mode = self::MODE_OPTIONS[$this->modeAnalisis];
            $service = new KMeansService($mode['k']);

            if ($mode['label'] !== null) {
                $service->setFixedLabel($mode['label']);
            }

            if ($this->centroidManual) {
                $centroids = [];
                for ($i = 0; $i < $mode['k']; $i++) {
                    $row = [];
                    foreach (RekapGiziDesa::FITUR_KEYS as $key) {
                        $row[$key] = (float) $this->centroidInputs[$i][$key];
                    }
                    $centroids[] = $row;
                }
                $service->setInitialCentroids($centroids);
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
