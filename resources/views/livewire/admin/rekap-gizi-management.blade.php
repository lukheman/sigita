<div>
    <x-admin.page-header title="Rekap Gizi Desa" subtitle="Kelola data agregat gizi per desa per periode">
        <x-slot:actions>
            <x-admin.button variant="outline" icon="fas fa-file-excel" wire:click="openImportModal">
                Import Excel
            </x-admin.button>
            <x-admin.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Tambah Rekap
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

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

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <x-admin.stat-card icon="fas fa-baby" label="Total Balita" :value="$totalBalita" variant="primary" />
        </div>
        <div class="col-md-4">
            <x-admin.stat-card icon="fas fa-balance-scale" label="Ditimbang" :value="$totalDitimbang" variant="info" />
        </div>
        <div class="col-md-4">
            <x-admin.stat-card icon="fas fa-exclamation-triangle" label="Stunting" :value="$totalStunting" variant="danger" />
        </div>
    </div>

    <div class="modern-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">Data Rekap</h5>
            <div class="d-flex flex-wrap gap-2">
                <select class="form-select" style="max-width: 160px;" wire:model.live="filterPeriode">
                    <option value="">Semua Periode</option>
                    @foreach($periodeOptions as $val => $label)
                        <option value="{{ $val }}">{{ \App\Models\RekapGiziDesa::formatPeriode($label) }}</option>
                    @endforeach
                </select>
                <div class="input-group" style="max-width: 220px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Cari desa..."
                        wire:model.live.debounce.300ms="search" style="border-left: none;">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Desa</th>
                        <th>Periode</th>
                        <th>Balita</th>
                        <th>Ditimbang</th>
                        <th>Stunting</th>
                        <th>Gizi Kurang</th>
                        <th>BB Kurang</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekapList as $index => $rekap)
                        <tr wire:key="rekap-{{ $rekap->id }}">
                            <td>{{ $rekapList->firstItem() + $index }}</td>
                            <td><div class="fw-semibold">{{ $rekap->desa->nama_desa }}</div></td>
                            <td><x-admin.badge variant="info">{{ $rekap->periode_label }}</x-admin.badge></td>
                            <td>{{ $rekap->jumlah_balita }}</td>
                            <td>{{ $rekap->jumlah_ditimbang }}</td>
                            <td>{{ $rekap->jumlah_stunting ?? '-' }}</td>
                            <td>{{ $rekap->jumlah_gizi_kurang ?? '-' }}</td>
                            <td>{{ $rekap->jumlah_bb_kurang ?? '-' }}</td>
                            <td>
                                <button class="action-btn action-btn-edit" wire:click="openEditModal({{ $rekap->id }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn action-btn-delete" wire:click="confirmDelete({{ $rekap->id }})" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <x-admin.empty-state title="Belum ada data" description="Tambah rekap atau import Excel." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $rekapList->links() }}</div>
    </div>

    @if($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom">
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">{{ $editingId ? 'Edit Rekap' : 'Tambah Rekap' }}</h5>
                    <button class="modal-close-btn" wire:click="closeModal">&times;</button>
                </div>
                <form wire:submit="save">
                    <div class="mb-3">
                        <label class="form-label">Desa</label>
                        <select class="form-select form-control" wire:model="desa_id">
                            <option value="">Pilih desa</option>
                            @foreach($desaOptions as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('desa_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Periode</label>
                        <input type="month" class="form-control" wire:model="periode">
                        <small class="text-muted">Ditampilkan sebagai misal "Jan 2026".</small>
                        @error('periode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Jumlah Balita</label>
                            <input type="number" min="0" class="form-control" wire:model="jumlah_balita">
                            @error('jumlah_balita') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Ditimbang</label>
                            <input type="number" min="0" class="form-control" wire:model="jumlah_ditimbang">
                            @error('jumlah_ditimbang') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label">Stunting</label>
                            <input type="number" min="0" class="form-control" placeholder="kosong = NULL" wire:model="jumlah_stunting">
                            @error('jumlah_stunting') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Gizi Kurang</label>
                            <input type="number" min="0" class="form-control" placeholder="kosong = NULL" wire:model="jumlah_gizi_kurang">
                            @error('jumlah_gizi_kurang') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">BB Kurang</label>
                            <input type="number" min="0" class="form-control" placeholder="kosong = NULL" wire:model="jumlah_bb_kurang">
                            @error('jumlah_bb_kurang') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" rows="2" wire:model="catatan"></textarea>
                    </div>
                    <p class="text-muted small">Kosongkan Stunting / Gizi Kurang / BB Kurang jika data belum tersedia — akan tersimpan sebagai NULL, bukan 0.</p>
                    <div class="d-flex justify-content-end gap-2">
                        <x-admin.button variant="outline" wire:click="closeModal" type="button">Batal</x-admin.button>
                        <x-admin.button variant="primary" type="submit">Simpan</x-admin.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <x-admin.confirm-modal :show="$showDeleteModal" title="Hapus Rekap?"
        message="Data rekap yang dihapus tidak dapat dikembalikan."
        confirm-text="Hapus" cancel-text="Batal" on-confirm="delete" on-cancel="cancelDelete" variant="danger"
        icon="fas fa-exclamation-triangle" />

    @if($showImportModal)
        <div class="modal-backdrop-custom" wire:click.self="closeImportModal">
            <div class="modal-content-custom">
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">Import Rekap Excel/CSV</h5>
                    <button class="modal-close-btn" wire:click="closeImportModal">&times;</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Periode default (jika kolom Periode kosong)</label>
                    <input type="month" class="form-control" wire:model="importPeriode">
                    @error('importPeriode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">File Excel/CSV</label>
                    <input type="file" class="form-control" wire:model="excelFile" accept=".xlsx,.xls,.csv">
                    @error('excelFile') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="d-flex gap-2 mb-3">
                    <x-admin.button variant="outline" icon="fas fa-download" wire:click="downloadTemplate">Template</x-admin.button>
                    <x-admin.button variant="primary" icon="fas fa-upload" wire:click="import">Import</x-admin.button>
                </div>
                @if($importProcessed)
                    <x-admin.alert variant="{{ $importSuccessCount > 0 ? 'success' : 'warning' }}" class="mb-2">
                        Berhasil: {{ $importSuccessCount }} baris.
                    </x-admin.alert>
                    @foreach($importErrors as $err)
                        <div class="text-danger small">{{ $err }}</div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif
</div>
