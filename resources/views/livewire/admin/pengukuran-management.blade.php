<div>
    {{-- Page Header --}}
    <x-admin.page-header title="Manajemen Pengukuran" subtitle="Kelola data pengukuran bulanan balita">
        <x-slot:actions>
            <x-admin.button variant="outline" icon="fas fa-file-excel" wire:click="openImportModal">
                Import Excel
            </x-admin.button>
            <x-admin.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Tambah Pengukuran
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
            <x-admin.stat-card icon="fas fa-ruler" label="Total Pengukuran Bulan Ini" :value="$totalPengukuran"
                variant="primary" />
        </div>
    </div>

    {{-- Pengukuran Table Card --}}
    <div class="modern-card">
        {{-- Search and Filters --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">Data Pengukuran</h5>
            <div class="d-flex flex-wrap gap-2">
                {{-- Filter Bulan --}}
                <select class="form-select"
                    style="max-width: 140px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary);"
                    wire:model.live="filterBulan">
                    <option value="">Semua Bulan</option>
                    @foreach($bulanOptions as $num => $nama)
                        <option value="{{ $num }}">{{ $nama }}</option>
                    @endforeach
                </select>

                {{-- Filter Tahun --}}
                <select class="form-select"
                    style="max-width: 100px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary);"
                    wire:model.live="filterTahun">
                    <option value="">Tahun</option>
                    @foreach($tahunOptions as $tahun)
                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endforeach
                </select>

                {{-- Filter Desa --}}
                <select class="form-select"
                    style="max-width: 160px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary);"
                    wire:model.live="filterDesa">
                    <option value="">Semua Desa</option>
                    @foreach($desaOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>

                {{-- Search --}}
                <div class="input-group" style="max-width: 220px;">
                    <span class="input-group-text"
                        style="background: var(--input-bg); border-color: var(--border-color);">
                        <i class="fas fa-search" style="color: var(--text-muted);"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Cari nama balita..."
                        wire:model.live.debounce.300ms="search" style="border-left: none;">
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Balita</th>
                        <th>Desa</th>
                        <th>Usia</th>
                        <th>BB (kg)</th>
                        <th>TB (cm)</th>
                        <th>Catatan</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pengukuranList as $index => $pengukuran)
                        <tr wire:key="pengukuran-{{ $pengukuran->id }}">
                            <td style="color: var(--text-secondary);">{{ $pengukuranList->firstItem() + $index }}</td>
                            <td style="color: var(--text-secondary);">{{ $pengukuran->tanggal_ukur->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-semibold" style="color: var(--text-primary);">
                                    {{ $pengukuran->balita->nama_lengkap }}
                                </div>
                                <small
                                    class="text-muted">{{ $pengukuran->balita->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</small>
                            </td>
                            <td style="color: var(--text-secondary);">{{ $pengukuran->balita->desa->nama_desa }}</td>
                            <td>
                                <x-admin.badge variant="info">
                                    {{ $pengukuran->usia_bulan }} bln
                                </x-admin.badge>
                            </td>
                            <td style="color: var(--text-primary); font-weight: 500;">
                                {{ number_format($pengukuran->berat_badan, 2) }}
                            </td>
                            <td style="color: var(--text-primary); font-weight: 500;">
                                {{ number_format($pengukuran->tinggi_badan, 2) }}
                            </td>
                            <td style="color: var(--text-secondary); max-width: 150px;" class="text-truncate">
                                {{ $pengukuran->catatan ?? '-' }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="action-btn action-btn-edit"
                                        wire:click="openEditModal({{ $pengukuran->id }})" title="Edit pengukuran">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn action-btn-delete"
                                        wire:click="confirmDelete({{ $pengukuran->id }})" title="Hapus pengukuran">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <x-admin.empty-state icon="fas fa-ruler" title="Belum ada data pengukuran"
                                    description="Mulai tambahkan data pengukuran balita." size="sm" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($pengukuranList->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $pengukuranList->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" style="max-width: 600px;" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        <i class="fas fa-ruler me-2" style="color: var(--primary-color);"></i>
                        {{ $editingId ? 'Edit Data Pengukuran' : 'Tambah Pengukuran Baru' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    {{-- Balita Selection --}}
                    <div class="mb-3">
                        <label for="balita_id" class="form-label">Pilih Balita <span
                                style="color: var(--danger-color);">*</span></label>
                        <select class="form-select @error('balita_id') is-invalid @enderror" id="balita_id"
                            wire:model.live="balita_id">
                            <option value="">-- Pilih Balita --</option>
                            @foreach($balitaOptions as $balita)
                                <option value="{{ $balita->id }}">
                                    {{ $balita->nama_lengkap }} - {{ $balita->desa->nama_desa }}
                                    ({{ $balita->jenis_kelamin === 'L' ? 'L' : 'P' }})
                                </option>
                            @endforeach
                        </select>
                        @error('balita_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Menampilkan maksimal 50 balita. Gunakan pencarian jika tidak
                            ditemukan.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_ukur" class="form-label">Tanggal Pengukuran <span
                                    style="color: var(--danger-color);">*</span></label>
                            <input type="date" class="form-control @error('tanggal_ukur') is-invalid @enderror"
                                id="tanggal_ukur" wire:model.live="tanggal_ukur" max="{{ date('Y-m-d') }}">
                            @error('tanggal_ukur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="usia_bulan" class="form-label">Usia (bulan) <span
                                    style="color: var(--danger-color);">*</span></label>
                            <input type="number" class="form-control @error('usia_bulan') is-invalid @enderror"
                                id="usia_bulan" wire:model="usia_bulan" placeholder="0-60" min="0" max="60">
                            @error('usia_bulan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Usia dihitung otomatis dari tanggal lahir</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="berat_badan" class="form-label">Berat Badan (kg) <span
                                    style="color: var(--danger-color);">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('berat_badan') is-invalid @enderror"
                                id="berat_badan" wire:model="berat_badan" placeholder="Contoh: 12.50" min="0.5" max="50">
                            @error('berat_badan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tinggi_badan" class="form-label">Tinggi Badan (cm) <span
                                    style="color: var(--danger-color);">*</span></label>
                            <input type="number" step="0.01"
                                class="form-control @error('tinggi_badan') is-invalid @enderror" id="tinggi_badan"
                                wire:model="tinggi_badan" placeholder="Contoh: 85.50" min="30" max="150">
                            @error('tinggi_badan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ASI Eksklusif & Akses Air Bersih --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="asi_eksklusif"
                                    wire:model="asi_eksklusif" style="width: 3em; height: 1.5em;">
                                <label class="form-check-label ms-2" for="asi_eksklusif"
                                    style="color: var(--text-primary);">
                                    <i class="fas fa-baby-carriage me-1" style="color: var(--primary-color);"></i>
                                    ASI Eksklusif
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Apakah bayi mendapat ASI eksklusif?</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="akses_air_bersih"
                                    wire:model="akses_air_bersih" style="width: 3em; height: 1.5em;">
                                <label class="form-check-label ms-2" for="akses_air_bersih"
                                    style="color: var(--text-primary);">
                                    <i class="fas fa-tint me-1" style="color: var(--secondary-color);"></i>
                                    Akses Air Bersih
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">Apakah keluarga memiliki akses air bersih?</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="catatan" class="form-label">Catatan</label>
                        <textarea class="form-control @error('catatan') is-invalid @enderror" id="catatan"
                            wire:model="catatan" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <x-admin.button type="button" variant="outline" wire:click="closeModal">
                            Batal
                        </x-admin.button>
                        <x-admin.button type="submit" variant="primary">
                            {{ $editingId ? 'Simpan Perubahan' : 'Simpan Pengukuran' }}
                        </x-admin.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <x-admin.confirm-modal :show="$showDeleteModal" title="Hapus Pengukuran"
        message="Apakah Anda yakin ingin menghapus data pengukuran ini? Tindakan ini tidak dapat dibatalkan."
        confirm-text="Hapus" cancel-text="Batal" on-confirm="delete" on-cancel="cancelDelete" variant="danger"
        icon="fas fa-exclamation-triangle" />

    {{-- Import Excel Modal --}}
    @if ($showImportModal)
        <div class="modal-backdrop-custom" wire:click.self="closeImportModal">
            <div class="modal-content-custom" style="max-width: 600px;" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        <i class="fas fa-file-excel me-2" style="color: var(--success-color);"></i>
                        Import Data dari Excel
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeImportModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-4">
                    @if (!$importProcessed)
                        {{-- Upload Form --}}
                        <div class="mb-4">
                            <p class="text-muted mb-3">
                                Upload file Excel (.xlsx, .xls) atau CSV dengan format yang sesuai.
                                <a href="#" wire:click.prevent="downloadTemplate" class="text-primary">
                                    <i class="fas fa-download me-1"></i>Download Template
                                </a>
                            </p>

                            <div class="mb-3">
                                <label class="form-label" style="color: var(--text-primary);">
                                    Pilih File <span class="text-danger">*</span>
                                </label>
                                <input type="file" class="form-control @error('excelFile') is-invalid @enderror"
                                    wire:model="excelFile" accept=".xlsx,.xls,.csv"
                                    style="background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary);">
                                @error('excelFile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- File Preview --}}
                            @if ($excelFile)
                                <div class="alert"
                                    style="background: rgba(var(--success-rgb), 0.1); border-color: var(--success-color); color: var(--text-primary);">
                                    <i class="fas fa-check-circle me-2" style="color: var(--success-color);"></i>
                                    File dipilih: <strong>{{ $excelFile->getClientOriginalName() }}</strong>
                                    ({{ number_format($excelFile->getSize() / 1024, 2) }} KB)
                                </div>
                            @endif

                            {{-- Format Info --}}
                            <div class="alert"
                                style="background: rgba(var(--info-rgb), 0.1); border-color: var(--info-color); color: var(--text-secondary);">
                                <strong><i class="fas fa-info-circle me-2"></i>Format Kolom:</strong>
                                <div class="mt-2" style="font-size: 0.85rem;">
                                    No, Nama Desa, Nama Posyandu, JK (L/P), Umur (Bln), BB/U, TB/U (Stunting), BB/TB, ASI
                                    Eksklusif (Ya/Tidak), Akses Air Bersih
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <x-admin.button type="button" variant="outline" wire:click="closeImportModal">
                                Batal
                            </x-admin.button>
                            <x-admin.button type="button" variant="success" wire:click="import" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="import">
                                    <i class="fas fa-upload me-2"></i>Import Data
                                </span>
                                <span wire:loading wire:target="import">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Memproses...
                                </span>
                            </x-admin.button>
                        </div>
                    @else
                        {{-- Import Results --}}
                        <div class="mb-4">
                            @if ($importSuccessCount > 0)
                                <div class="alert"
                                    style="background: rgba(var(--success-rgb), 0.1); border-color: var(--success-color); color: var(--text-primary);">
                                    <i class="fas fa-check-circle me-2" style="color: var(--success-color);"></i>
                                    <strong>{{ $importSuccessCount }}</strong> data berhasil diimport.
                                </div>
                            @endif

                            @if (count($importErrors) > 0)
                                <div class="alert"
                                    style="background: rgba(var(--danger-rgb), 0.1); border-color: var(--danger-color); color: var(--text-primary);">
                                    <strong><i class="fas fa-exclamation-triangle me-2" style="color: var(--danger-color);"></i>
                                        {{ count($importErrors) }} error ditemukan:</strong>
                                    <ul class="mb-0 mt-2" style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($importErrors as $error)
                                            <li style="font-size: 0.85rem;">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end">
                            <x-admin.button type="button" variant="primary" wire:click="closeImportModal">
                                Tutup
                            </x-admin.button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>