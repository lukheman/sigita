<?php

namespace App\Livewire\Admin;

use App\Models\Desa;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.livewire-layout')]
#[Title('Manajemen Desa - SIGITA')]
class DesaManagement extends Component
{
    use WithPagination;

    // Search
    #[Url(as: 'q')]
    public string $search = '';

    // Form fields
    public string $nama_desa = '';
    public ?string $keterangan = '';

    // State
    public ?int $editingId = null;
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    protected function rules(): array
    {
        $rules = [
            'nama_desa' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ];

        if ($this->editingId) {
            $rules['nama_desa'][] = 'unique:desa,nama_desa,' . $this->editingId;
        } else {
            $rules['nama_desa'][] = 'unique:desa,nama_desa';
        }

        return $rules;
    }

    protected $messages = [
        'nama_desa.required' => 'Nama desa wajib diisi.',
        'nama_desa.unique' => 'Nama desa sudah terdaftar.',
    ];

    public function updatedSearch(): void
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
        $desa = Desa::findOrFail($id);
        $this->editingId = $id;
        $this->nama_desa = $desa->nama_desa;
        $this->keterangan = $desa->keterangan ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            $desa = Desa::findOrFail($this->editingId);
            $desa->update($validated);
            session()->flash('success', 'Desa berhasil diperbarui.');
        } else {
            Desa::create($validated);
            session()->flash('success', 'Desa berhasil ditambahkan.');
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
            $desa = Desa::find($this->deletingId);

            // Check if desa has balita
            if ($desa && $desa->balita()->count() > 0) {
                session()->flash('error', 'Tidak dapat menghapus desa yang masih memiliki data balita.');
            } else {
                Desa::destroy($this->deletingId);
                session()->flash('success', 'Desa berhasil dihapus.');
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
        $this->nama_desa = '';
        $this->keterangan = '';
        $this->editingId = null;
    }

    public function render()
    {
        $desaList = Desa::query()
            ->withCount('balita')
            ->when($this->search, function ($query) {
                $query->where('nama_desa', 'like', '%' . $this->search . '%')
                    ->orWhere('keterangan', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nama_desa', 'asc')
            ->paginate(10);

        return view('livewire.admin.desa-management', [
            'desaList' => $desaList,
        ]);
    }
}
