<div>
    {{-- Page Header --}}
    <x-admin.page-header title="Manajemen Desa" subtitle="Kelola data desa di wilayah Kecamatan Tanggetada">
        <x-slot:actions>
            <x-admin.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Tambah Desa
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <x-admin.alert variant="success" title="Berhasil!" class="mb-4">
            {{ session('success') }}
        </x-admin.alert>
    @endif

    @if (session('error'))
        <x-admin.alert variant="danger" title="Gagal!" class="mb-4">
            {{ session('error') }}
        </x-admin.alert>
    @endif

    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <x-admin.stat-card icon="fas fa-map-marker-alt" label="Total Desa" :value="$desaList->total()"
                variant="primary" />
        </div>
    </div>

    {{-- Desa Table Card --}}
    <div class="modern-card">
        {{-- Search and Filters --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">Daftar Desa</h5>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text" style="background: var(--input-bg); border-color: var(--border-color);">
                    <i class="fas fa-search" style="color: var(--text-muted);"></i>
                </span>
                <input type="text" class="form-control" placeholder="Cari desa..."
                    wire:model.live.debounce.300ms="search" style="border-left: none;">
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Desa</th>
                        <th>Keterangan</th>
                        <th>Jumlah Balita</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($desaList as $index => $desa)
                        <tr wire:key="desa-{{ $desa->id }}">
                            <td style="color: var(--text-secondary);">{{ $desaList->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold" style="color: var(--text-primary);">{{ $desa->nama_desa }}</div>
                            </td>
                            <td style="color: var(--text-secondary);">{{ $desa->keterangan ?? '-' }}</td>
                            <td>
                                <x-admin.badge variant="{{ $desa->balita_count > 0 ? 'primary' : 'info' }}">
                                    {{ $desa->balita_count }} Balita
                                </x-admin.badge>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="action-btn action-btn-edit" wire:click="openEditModal({{ $desa->id }})"
                                        title="Edit desa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn action-btn-delete" wire:click="confirmDelete({{ $desa->id }})"
                                        title="Hapus desa">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <x-admin.empty-state icon="fas fa-map-marker-alt" title="Belum ada data desa"
                                    description="Mulai tambahkan data desa untuk wilayah Anda." size="sm" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($desaList->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $desaList->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        <i class="fas fa-map-marker-alt me-2" style="color: var(--primary-color);"></i>
                        {{ $editingId ? 'Edit Desa' : 'Tambah Desa Baru' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="mb-3">
                        <label for="nama_desa" class="form-label">Nama Desa <span
                                style="color: var(--danger-color);">*</span></label>
                        <input type="text" class="form-control @error('nama_desa') is-invalid @enderror" id="nama_desa"
                            wire:model="nama_desa" placeholder="Masukkan nama desa">
                        @error('nama_desa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan"
                            wire:model="keterangan" rows="3" placeholder="Masukkan keterangan (opsional)"></textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <x-admin.button type="button" variant="outline" wire:click="closeModal">
                            Batal
                        </x-admin.button>
                        <x-admin.button type="submit" variant="primary">
                            {{ $editingId ? 'Simpan Perubahan' : 'Tambah Desa' }}
                        </x-admin.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <x-admin.confirm-modal :show="$showDeleteModal" title="Hapus Desa"
        message="Apakah Anda yakin ingin menghapus desa ini? Tindakan ini tidak dapat dibatalkan." confirm-text="Hapus"
        cancel-text="Batal" on-confirm="delete" on-cancel="cancelDelete" variant="danger"
        icon="fas fa-exclamation-triangle" />
</div>