<?php

namespace App\Livewire\Admin;

use App\Models\Balita;
use App\Models\Desa;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.livewire-layout')]
#[Title('Manajemen Balita - SIGITA')]
class BalitaManagement extends Component
{
    use WithPagination;

    // Search & Filter
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'desa')]
    public string $filterDesa = '';

    #[Url(as: 'jk')]
    public string $filterJenisKelamin = '';

    // Form fields
    public string $desa_id = '';
    public string $nik = '';
    public string $nama_lengkap = '';
    public string $jenis_kelamin = '';
    public string $nama_orang_tua = '';
    public string $tanggal_lahir = '';

    // State
    public ?int $editingId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public bool $showDetailModal = false;
    public ?Balita $detailBalita = null;

    protected function rules(): array
    {
        $rules = [
            'desa_id' => ['required', 'exists:desa,id'],
            'nik' => ['nullable', 'string', 'size:16'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'nama_orang_tua' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
        ];

        if ($this->editingId) {
            $rules['nik'][] = 'unique:balita,nik,' . $this->editingId;
        } else {
            $rules['nik'][] = 'unique:balita,nik';
        }

        return $rules;
    }

    protected $messages = [
        'desa_id.required' => 'Desa wajib dipilih.',
        'desa_id.exists' => 'Desa tidak valid.',
        'nik.size' => 'NIK harus 16 digit.',
        'nik.unique' => 'NIK sudah terdaftar.',
        'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
        'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
        'nama_orang_tua.required' => 'Nama orang tua wajib diisi.',
        'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
        'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDesa(): void
    {
        $this->resetPage();
    }

    public function updatedFilterJenisKelamin(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $balita = Balita::findOrFail($id);
        $this->editingId = $id;
        $this->desa_id = (string) $balita->desa_id;
        $this->nik = $balita->nik ?? '';
        $this->nama_lengkap = $balita->nama_lengkap;
        $this->jenis_kelamin = $balita->jenis_kelamin;
        $this->nama_orang_tua = $balita->nama_orang_tua;
        $this->tanggal_lahir = Carbon::parse($balita->tanggal_lahir)->format('Y-m-d');
        $this->showModal = true;
    }

    public function openDetailModal(int $id): void
    {
        $this->detailBalita = Balita::with([
            'desa',
            'pengukuran' => function ($q) {
                $q->orderBy('tanggal_ukur', 'desc')->limit(5);
            }
        ])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailBalita = null;
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Convert empty nik to null
        if (empty($validated['nik'])) {
            $validated['nik'] = null;
        }

        if ($this->editingId) {
            $balita = Balita::findOrFail($this->editingId);
            $balita->update($validated);
            session()->flash('success', 'Data balita berhasil diperbarui.');
        } else {
            Balita::create($validated);
            session()->flash('success', 'Data balita berhasil ditambahkan.');
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
            $balita = Balita::find($this->deletingId);

            if ($balita && $balita->pengukuran()->count() > 0) {
                session()->flash('error', 'Tidak dapat menghapus balita yang masih memiliki data pengukuran.');
            } else {
                Balita::destroy($this->deletingId);
                session()->flash('success', 'Data balita berhasil dihapus.');
            }
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
        $this->nik = '';
        $this->nama_lengkap = '';
        $this->jenis_kelamin = '';
        $this->nama_orang_tua = '';
        $this->tanggal_lahir = '';
        $this->editingId = null;
    }

    public function render()
    {
        $balitaList = Balita::query()
            ->with('desa')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_lengkap', 'like', '%' . $this->search . '%')
                        ->orWhere('nik', 'like', '%' . $this->search . '%')
                        ->orWhere('nama_orang_tua', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterDesa, function ($query) {
                $query->where('desa_id', $this->filterDesa);
            })
            ->when($this->filterJenisKelamin, function ($query) {
                $query->where('jenis_kelamin', $this->filterJenisKelamin);
            })
            ->orderBy('nama_lengkap', 'asc')
            ->paginate(10);

        $desaOptions = Desa::orderBy('nama_desa')->pluck('nama_desa', 'id')->toArray();

        // Statistics
        $totalBalita = Balita::count();
        $totalLakiLaki = Balita::where('jenis_kelamin', 'L')->count();
        $totalPerempuan = Balita::where('jenis_kelamin', 'P')->count();

        return view('livewire.admin.balita-management', [
            'balitaList' => $balitaList,
            'desaOptions' => $desaOptions,
            'totalBalita' => $totalBalita,
            'totalLakiLaki' => $totalLakiLaki,
            'totalPerempuan' => $totalPerempuan,
        ]);
    }
}
