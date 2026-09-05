<?php

namespace App\Livewire\Admin;

use App\Imports\RekapGiziImport;
use App\Models\Desa;
use App\Models\RekapGiziDesa;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.admin.livewire-layout')]
#[Title('Rekap Gizi Desa - SIGITA')]
class RekapGiziManagement extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'periode')]
    public string $filterPeriode = '';

    // Form fields
    public string $desa_id = '';
    public string $periode = '';
    public string $jumlah_balita = '';
    public string $jumlah_ditimbang = '';
    public string $jumlah_stunting = '';
    public string $jumlah_gizi_kurang = '';
    public string $jumlah_bb_kurang = '';
    public string $catatan = '';

    // State
    public ?int $editingId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    // Import Excel
    public $excelFile = null;
    public string $importPeriode = '';
    public bool $showImportModal = false;
    public array $importErrors = [];
    public int $importSuccessCount = 0;
    public bool $importProcessed = false;

    protected function rules(): array
    {
        return [
            'desa_id' => ['required', 'exists:desa,id'],
            'periode' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'jumlah_balita' => ['required', 'integer', 'min:0', 'max:100000'],
            'jumlah_ditimbang' => ['required', 'integer', 'min:0', 'max:100000'],
            'jumlah_stunting' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'jumlah_gizi_kurang' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'jumlah_bb_kurang' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected $messages = [
        'desa_id.required' => 'Desa wajib dipilih.',
        'desa_id.exists' => 'Desa tidak valid.',
        'periode.required' => 'Periode wajib diisi (format YYYY-MM).',
        'periode.regex' => 'Format periode harus YYYY-MM, misal 2026-01.',
        'jumlah_balita.required' => 'Jumlah balita wajib diisi.',
        'jumlah_ditimbang.required' => 'Jumlah ditimbang wajib diisi.',
    ];

    public function mount(): void
    {
        if (empty($this->filterPeriode)) {
            $this->filterPeriode = RekapGiziDesa::query()
                ->orderBy('periode', 'desc')
                ->value('periode') ?? date('Y-m');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPeriode(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->periode = $this->filterPeriode ?: date('Y-m');
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $rekap = RekapGiziDesa::findOrFail($id);
        $this->editingId = $id;
        $this->desa_id = (string) $rekap->desa_id;
        $this->periode = $rekap->periode;
        $this->jumlah_balita = (string) $rekap->jumlah_balita;
        $this->jumlah_ditimbang = (string) $rekap->jumlah_ditimbang;
        $this->jumlah_stunting = $rekap->jumlah_stunting === null ? '' : (string) $rekap->jumlah_stunting;
        $this->jumlah_gizi_kurang = $rekap->jumlah_gizi_kurang === null ? '' : (string) $rekap->jumlah_gizi_kurang;
        $this->jumlah_bb_kurang = $rekap->jumlah_bb_kurang === null ? '' : (string) $rekap->jumlah_bb_kurang;
        $this->catatan = $rekap->catatan ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Validasi bisnis
        if ((int) $validated['jumlah_ditimbang'] > (int) $validated['jumlah_balita']) {
            $this->addError('jumlah_ditimbang', 'Jumlah ditimbang tidak boleh melebihi jumlah balita.');

            return;
        }
        foreach (['jumlah_stunting', 'jumlah_gizi_kurang', 'jumlah_bb_kurang'] as $field) {
            if ($validated[$field] !== null && $validated[$field] !== '' && (int) $validated[$field] > (int) $validated['jumlah_ditimbang']) {
                $this->addError($field, 'Nilai tidak boleh melebihi jumlah ditimbang.');

                return;
            }
        }

        // Normalisasi string kosong -> null (bedakan NULL vs 0)
        foreach (['jumlah_stunting', 'jumlah_gizi_kurang', 'jumlah_bb_kurang'] as $field) {
            if ($validated[$field] === '') {
                $validated[$field] = null;
            }
        }
        $validated['catatan'] = empty($validated['catatan']) ? null : $validated['catatan'];
        $validated['created_by'] = Auth::id();

        // Cegah duplikasi desa + periode (kecuali sedang edit baris yang sama)
        $exists = RekapGiziDesa::where('desa_id', $validated['desa_id'])
            ->where('periode', $validated['periode'])
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();
        if ($exists) {
            $this->addError('periode', 'Rekap desa ini pada periode tersebut sudah ada.');

            return;
        }

        if ($this->editingId) {
            RekapGiziDesa::findOrFail($this->editingId)->update($validated);
            session()->flash('success', 'Rekap gizi berhasil diperbarui.');
        } else {
            RekapGiziDesa::create($validated);
            session()->flash('success', 'Rekap gizi berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            RekapGiziDesa::destroy($this->deletingId);
            session()->flash('success', 'Rekap gizi berhasil dihapus.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    protected function resetForm(): void
    {
        $this->desa_id = '';
        $this->periode = '';
        $this->jumlah_balita = '';
        $this->jumlah_ditimbang = '';
        $this->jumlah_stunting = '';
        $this->jumlah_gizi_kurang = '';
        $this->jumlah_bb_kurang = '';
        $this->catatan = '';
        $this->editingId = null;
    }

    // Import Excel Methods
    public function openImportModal(): void
    {
        $this->excelFile = null;
        $this->importPeriode = $this->filterPeriode ?: date('Y-m');
        $this->importErrors = [];
        $this->importSuccessCount = 0;
        $this->importProcessed = false;
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->excelFile = null;
        $this->importErrors = [];
        $this->importSuccessCount = 0;
        $this->importProcessed = false;
    }

    public function import(): void
    {
        $this->validate([
            'excelFile' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'importPeriode' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ], [
            'excelFile.required' => 'Pilih file Excel untuk diimport.',
            'excelFile.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'excelFile.max' => 'Ukuran file maksimal 5MB.',
            'importPeriode.regex' => 'Format periode harus YYYY-MM.',
        ]);

        try {
            $import = new RekapGiziImport($this->importPeriode);
            Excel::import($import, $this->excelFile->getRealPath());

            $this->importSuccessCount = $import->successCount;
            $this->importErrors = $import->errors;
            $this->importProcessed = true;

            if ($import->successCount > 0) {
                session()->flash('message', "Berhasil mengimport {$import->successCount} rekap desa.");
            }
        } catch (\Exception $e) {
            $this->importErrors[] = 'Error: ' . $e->getMessage();
            $this->importProcessed = true;
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_rekap_gizi.csv"',
        ];

        $columns = ['DESA', 'JUMLAH BALITA', 'BALITA DI TIMBANG', 'STUNTING', 'GIZI KURANG', 'BB KURANG'];
        $examples = [
            ['Lamedai', '80', '78', '18', '', ''],
            ['Lalonggolosua', '86', '82', '9', '5', '12'],
        ];

        $callback = function () use ($columns, $examples) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($examples as $ex) {
                fputcsv($file, $ex);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $rekapList = RekapGiziDesa::query()
            ->with('desa')
            ->when($this->search, fn($q) => $q->whereHas('desa', fn($qq) => $qq->where('nama_desa', 'like', '%' . $this->search . '%')))
            ->when($this->filterPeriode, fn($q) => $q->where('periode', $this->filterPeriode))
            ->orderBy('periode', 'desc')
            ->orderBy('id')
            ->paginate(20);

        $desaOptions = Desa::orderBy('nama_desa')->pluck('nama_desa', 'id')->toArray();
        $periodeOptions = RekapGiziDesa::query()
            ->distinct()->orderBy('periode', 'desc')->pluck('periode', 'periode')->toArray();

        // Ringkasan periode aktif
        $summaryQuery = RekapGiziDesa::when($this->filterPeriode, fn($q) => $q->where('periode', $this->filterPeriode));
        $totalBalita = (clone $summaryQuery)->sum('jumlah_balita');
        $totalDitimbang = (clone $summaryQuery)->sum('jumlah_ditimbang');
        $totalStunting = (clone $summaryQuery)->sum('jumlah_stunting');
        $belumLengkap = (clone $summaryQuery)
            ->where(fn($q) => $q->whereNull('jumlah_stunting')->orWhereNull('jumlah_gizi_kurang')->orWhereNull('jumlah_bb_kurang'))
            ->count();

        return view('livewire.admin.rekap-gizi-management', [
            'rekapList' => $rekapList,
            'desaOptions' => $desaOptions,
            'periodeOptions' => $periodeOptions,
            'totalBalita' => $totalBalita,
            'totalDitimbang' => $totalDitimbang,
            'totalStunting' => $totalStunting,
            'belumLengkap' => $belumLengkap,
        ]);
    }
}
