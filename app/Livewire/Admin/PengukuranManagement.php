<?php

namespace App\Livewire\Admin;

use App\Imports\PengukuranImport;
use App\Models\Balita;
use App\Models\Desa;
use App\Models\Pengukuran;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.admin.livewire-layout')]
#[Title('Manajemen Pengukuran - SIGITA')]
class PengukuranManagement extends Component
{
    use WithPagination, WithFileUploads;

    // Search & Filter
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'desa')]
    public string $filterDesa = '';

    #[Url(as: 'bulan')]
    public string $filterBulan = '';

    #[Url(as: 'tahun')]
    public string $filterTahun = '';

    // Form fields
    public string $balita_id = '';
    public string $tanggal_ukur = '';
    public string $usia_bulan = '';
    public string $berat_badan = '';
    public string $tinggi_badan = '';
    public bool $asi_eksklusif = false;
    public bool $akses_air_bersih = false;
    public string $catatan = '';

    // State
    public ?int $editingId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    // Import Excel
    public $excelFile = null;
    public bool $showImportModal = false;
    public array $importErrors = [];
    public int $importSuccessCount = 0;
    public bool $importProcessed = false;

    // Balita search for form
    public string $searchBalita = '';

    protected function rules(): array
    {
        return [
            'balita_id' => ['required', 'exists:balita,id'],
            'tanggal_ukur' => ['required', 'date', 'before_or_equal:today'],
            'usia_bulan' => ['required', 'integer', 'min:0', 'max:60'],
            'berat_badan' => ['required', 'numeric', 'min:0.5', 'max:50'],
            'tinggi_badan' => ['required', 'numeric', 'min:30', 'max:150'],
            'asi_eksklusif' => ['boolean'],
            'akses_air_bersih' => ['boolean'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected $messages = [
        'balita_id.required' => 'Balita wajib dipilih.',
        'balita_id.exists' => 'Balita tidak valid.',
        'tanggal_ukur.required' => 'Tanggal pengukuran wajib diisi.',
        'tanggal_ukur.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
        'usia_bulan.required' => 'Usia wajib diisi.',
        'usia_bulan.min' => 'Usia minimal 0 bulan.',
        'usia_bulan.max' => 'Usia maksimal 60 bulan.',
        'berat_badan.required' => 'Berat badan wajib diisi.',
        'berat_badan.min' => 'Berat badan minimal 0.5 kg.',
        'berat_badan.max' => 'Berat badan maksimal 50 kg.',
        'tinggi_badan.required' => 'Tinggi badan wajib diisi.',
        'tinggi_badan.min' => 'Tinggi badan minimal 30 cm.',
        'tinggi_badan.max' => 'Tinggi badan maksimal 150 cm.',
    ];

    public function mount(): void
    {
        // Set default filter to current month/year
        if (empty($this->filterBulan)) {
            $this->filterBulan = (string) date('n');
        }
        if (empty($this->filterTahun)) {
            $this->filterTahun = (string) date('Y');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDesa(): void
    {
        $this->resetPage();
    }

    public function updatedFilterBulan(): void
    {
        $this->resetPage();
    }

    public function updatedFilterTahun(): void
    {
        $this->resetPage();
    }

    public function updatedBalitaId(): void
    {
        // Auto-calculate age when balita is selected
        if ($this->balita_id && $this->tanggal_ukur) {
            $this->calculateAge();
        }
    }

    public function updatedTanggalUkur(): void
    {
        // Auto-calculate age when date changes
        if ($this->balita_id && $this->tanggal_ukur) {
            $this->calculateAge();
        }
    }

    protected function calculateAge(): void
    {
        $balita = Balita::find($this->balita_id);
        if ($balita && $this->tanggal_ukur) {
            $tanggalLahir = Carbon::parse($balita->tanggal_lahir);
            $tanggalUkur = Carbon::parse($this->tanggal_ukur);
            $this->usia_bulan = (string) $tanggalLahir->diffInMonths($tanggalUkur);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->tanggal_ukur = date('Y-m-d');
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $pengukuran = Pengukuran::findOrFail($id);
        $this->editingId = $id;
        $this->balita_id = (string) $pengukuran->balita_id;
        $this->tanggal_ukur = Carbon::parse($pengukuran->tanggal_ukur)->format('Y-m-d');
        $this->usia_bulan = (string) $pengukuran->usia_bulan;
        $this->berat_badan = (string) $pengukuran->berat_badan;
        $this->tinggi_badan = (string) $pengukuran->tinggi_badan;
        $this->asi_eksklusif = (bool) $pengukuran->asi_eksklusif;
        $this->akses_air_bersih = (bool) $pengukuran->akses_air_bersih;
        $this->catatan = $pengukuran->catatan ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Convert empty catatan to null
        if (empty($validated['catatan'])) {
            $validated['catatan'] = null;
        }

        if ($this->editingId) {
            $pengukuran = Pengukuran::findOrFail($this->editingId);
            $pengukuran->update($validated);
            session()->flash('success', 'Data pengukuran berhasil diperbarui.');
        } else {
            Pengukuran::create($validated);
            session()->flash('success', 'Data pengukuran berhasil ditambahkan.');
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
            Pengukuran::destroy($this->deletingId);
            session()->flash('success', 'Data pengukuran berhasil dihapus.');
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
        $this->balita_id = '';
        $this->tanggal_ukur = '';
        $this->usia_bulan = '';
        $this->berat_badan = '';
        $this->tinggi_badan = '';
        $this->asi_eksklusif = false;
        $this->akses_air_bersih = false;
        $this->catatan = '';
        $this->editingId = null;
        $this->searchBalita = '';
    }

    // Import Excel Methods
    public function openImportModal(): void
    {
        $this->excelFile = null;
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
        ], [
            'excelFile.required' => 'Pilih file Excel untuk diimport.',
            'excelFile.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'excelFile.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $import = new PengukuranImport();
            Excel::import($import, $this->excelFile->getRealPath());

            $this->importSuccessCount = $import->successCount;
            $this->importErrors = $import->errors;
            $this->importProcessed = true;

            if ($import->successCount > 0) {
                session()->flash('message', "Berhasil mengimport {$import->successCount} data pengukuran.");
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
            'Content-Disposition' => 'attachment; filename="template_import_pengukuran.csv"',
        ];

        $columns = ['No', 'Nama Desa', 'Nama Posyandu', 'JK', 'Umur (Bln)', 'BB/U', 'TB/U (Stunting)', 'BB/TB', 'ASI Eksklusif', 'Akses Air Bersih'];
        $example = ['1', 'Tanggetada', 'Mawar I', 'L', '12', 'Gizi Baik', 'Normal', 'Gizi Baik', 'Ya', 'Perpipaan'];

        $callback = function () use ($columns, $example) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $pengukuranList = Pengukuran::query()
            ->with(['balita.desa'])
            ->when($this->search, function ($query) {
                $query->whereHas('balita', function ($q) {
                    $q->where('nama_lengkap', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterDesa, function ($query) {
                $query->whereHas('balita', function ($q) {
                    $q->where('desa_id', $this->filterDesa);
                });
            })
            ->when($this->filterBulan, function ($query) {
                $query->whereMonth('tanggal_ukur', $this->filterBulan);
            })
            ->when($this->filterTahun, function ($query) {
                $query->whereYear('tanggal_ukur', $this->filterTahun);
            })
            ->orderBy('tanggal_ukur', 'desc')
            ->paginate(15);

        $desaOptions = Desa::orderBy('nama_desa')->pluck('nama_desa', 'id')->toArray();

        // Balita options for form (limit to prevent performance issues)
        $balitaOptions = Balita::with('desa')
            ->when($this->searchBalita, function ($query) {
                $query->where('nama_lengkap', 'like', '%' . $this->searchBalita . '%');
            })
            ->orderBy('nama_lengkap')
            ->limit(50)
            ->get();

        // Statistics
        $totalPengukuran = Pengukuran::when($this->filterBulan, fn($q) => $q->whereMonth('tanggal_ukur', $this->filterBulan))
            ->when($this->filterTahun, fn($q) => $q->whereYear('tanggal_ukur', $this->filterTahun))
            ->count();

        // Month options
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

        // Year options (last 5 years)
        $currentYear = (int) date('Y');
        $tahunOptions = array_combine(
            range($currentYear - 4, $currentYear),
            range($currentYear - 4, $currentYear)
        );

        return view('livewire.admin.pengukuran-management', [
            'pengukuranList' => $pengukuranList,
            'desaOptions' => $desaOptions,
            'balitaOptions' => $balitaOptions,
            'bulanOptions' => $bulanOptions,
            'tahunOptions' => $tahunOptions,
            'totalPengukuran' => $totalPengukuran,
        ]);
    }
}
