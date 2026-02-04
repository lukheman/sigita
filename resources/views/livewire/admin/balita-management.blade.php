<div>
    {{-- Page Header --}}
    <x-admin.page-header title="Manajemen Balita" subtitle="Kelola data balita di seluruh wilayah">
        <x-slot:actions>
            <x-admin.button variant="primary" icon="fas fa-plus" wire:click="openCreateModal">
                Tambah Balita
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
            <x-admin.stat-card 
                icon="fas fa-baby" 
                label="Total Balita" 
                :value="$totalBalita" 
                variant="primary" 
            />
        </div>
        <div class="col-md-4">
            <x-admin.stat-card 
                icon="fas fa-male" 
                label="Laki-laki" 
                :value="$totalLakiLaki" 
                variant="secondary" 
            />
        </div>
        <div class="col-md-4">
            <x-admin.stat-card 
                icon="fas fa-female" 
                label="Perempuan" 
                :value="$totalPerempuan" 
                variant="danger" 
            />
        </div>
    </div>

    {{-- Balita Table Card --}}
    <div class="modern-card">
        {{-- Search and Filters --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">Daftar Balita</h5>
            <div class="d-flex flex-wrap gap-2">
                {{-- Filter Desa --}}
                <select class="form-select" style="max-width: 180px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary);"
                    wire:model.live="filterDesa">
                    <option value="">Semua Desa</option>
                    @foreach($desaOptions as $id => $nama)
                        <option value="{{ $id }}">{{ $nama }}</option>
                    @endforeach
                </select>

                {{-- Filter Jenis Kelamin --}}
                <select class="form-select" style="max-width: 150px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary);"
                    wire:model.live="filterJenisKelamin">
                    <option value="">Semua JK</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>

                {{-- Search --}}
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text" style="background: var(--input-bg); border-color: var(--border-color);">
                        <i class="fas fa-search" style="color: var(--text-muted);"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Cari nama/NIK..."
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
                        <th>Nama Balita</th>
                        <th>NIK</th>
                        <th>JK</th>
                        <th>Usia</th>
                        <th>Orang Tua</th>
                        <th>Desa</th>
                        <th style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($balitaList as $index => $balita)
                        <tr wire:key="balita-{{ $balita->id }}">
                            <td style="color: var(--text-secondary);">{{ $balitaList->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold" style="color: var(--text-primary);">{{ $balita->nama_lengkap }}</div>
                                <small class="text-muted">{{ $balita->tanggal_lahir->format('d M Y') }}</small>
                            </td>
                            <td style="color: var(--text-secondary);">{{ $balita->nik ?? '-' }}</td>
                            <td>
                                <x-admin.badge variant="{{ $balita->jenis_kelamin === 'L' ? 'primary' : 'danger' }}">
                                    {{ $balita->jenis_kelamin === 'L' ? 'L' : 'P' }}
                                </x-admin.badge>
                            </td>
                            <td style="color: var(--text-secondary);">{{ $balita->usiaFormatted() }}</td>
                            <td style="color: var(--text-secondary);">{{ $balita->nama_orang_tua }}</td>
                            <td style="color: var(--text-secondary);">{{ $balita->desa->nama_desa }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="action-btn" style="color: var(--success-color);" 
                                        wire:click="openDetailModal({{ $balita->id }})" title="Lihat detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn action-btn-edit" wire:click="openEditModal({{ $balita->id }})"
                                        title="Edit balita">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn action-btn-delete" wire:click="confirmDelete({{ $balita->id }})"
                                        title="Hapus balita">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <x-admin.empty-state
                                    icon="fas fa-baby"
                                    title="Belum ada data balita"
                                    description="Mulai tambahkan data balita untuk wilayah Anda."
                                    size="sm"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($balitaList->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $balitaList->links() }}
            </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" style="max-width: 600px;" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        <i class="fas fa-baby me-2" style="color: var(--primary-color);"></i>
                        {{ $editingId ? 'Edit Data Balita' : 'Tambah Balita Baru' }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="desa_id" class="form-label">Desa <span style="color: var(--danger-color);">*</span></label>
                            <select class="form-select @error('desa_id') is-invalid @enderror" id="desa_id" wire:model="desa_id">
                                <option value="">Pilih Desa...</option>
                                @foreach($desaOptions as $id => $nama)
                                    <option value="{{ $id }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                            @error('desa_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik"
                                wire:model="nik" placeholder="16 digit NIK" maxlength="16">
                            @error('nik')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap <span style="color: var(--danger-color);">*</span></label>
                        <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap"
                            wire:model="nama_lengkap" placeholder="Masukkan nama lengkap balita">
                        @error('nama_lengkap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span style="color: var(--danger-color);">*</span></label>
                            <select class="form-select @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" wire:model="jenis_kelamin">
                                <option value="">Pilih...</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span style="color: var(--danger-color);">*</span></label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir"
                                wire:model="tanggal_lahir" max="{{ date('Y-m-d') }}">
                            @error('tanggal_lahir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="nama_orang_tua" class="form-label">Nama Orang Tua <span style="color: var(--danger-color);">*</span></label>
                        <input type="text" class="form-control @error('nama_orang_tua') is-invalid @enderror" id="nama_orang_tua"
                            wire:model="nama_orang_tua" placeholder="Masukkan nama orang tua/wali">
                        @error('nama_orang_tua')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <x-admin.button type="button" variant="outline" wire:click="closeModal">
                            Batal
                        </x-admin.button>
                        <x-admin.button type="submit" variant="primary">
                            {{ $editingId ? 'Simpan Perubahan' : 'Tambah Balita' }}
                        </x-admin.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if ($showDetailModal && $detailBalita)
        <div class="modal-backdrop-custom" wire:click.self="closeDetailModal">
            <div class="modal-content-custom" style="max-width: 600px;" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        <i class="fas fa-baby me-2" style="color: var(--primary-color);"></i>
                        Detail Balita
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeDetailModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="user-avatar" style="width: 60px; height: 60px; font-size: 1.5rem; background: {{ $detailBalita->jenis_kelamin === 'L' ? 'var(--primary-color)' : 'var(--danger-color)' }};">
                            <i class="fas {{ $detailBalita->jenis_kelamin === 'L' ? 'fa-male' : 'fa-female' }}"></i>
                        </div>
                        <div>
                            <h4 class="mb-0" style="color: var(--text-primary);">{{ $detailBalita->nama_lengkap }}</h4>
                            <span class="text-muted">{{ $detailBalita->usiaFormatted() }}</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted d-block">NIK</small>
                            <span style="color: var(--text-primary);">{{ $detailBalita->nik ?? '-' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Jenis Kelamin</small>
                            <span style="color: var(--text-primary);">{{ $detailBalita->jenisKelaminLabel() }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Tanggal Lahir</small>
                            <span style="color: var(--text-primary);">{{ $detailBalita->tanggal_lahir->format('d F Y') }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Nama Orang Tua</small>
                            <span style="color: var(--text-primary);">{{ $detailBalita->nama_orang_tua }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Desa</small>
                            <span style="color: var(--text-primary);">{{ $detailBalita->desa->nama_desa }}</span>
                        </div>
                    </div>
                </div>

                {{-- Riwayat Pengukuran Terakhir --}}
                @if($detailBalita->pengukuran->count() > 0)
                    <h6 style="color: var(--text-primary);" class="mb-3">Pengukuran Terakhir</h6>
                    <div class="table-responsive">
                        <table class="table table-sm" style="color: var(--text-primary);">
                            <thead>
                                <tr>
                                    <th style="color: var(--text-muted); font-size: 0.75rem;">Tanggal</th>
                                    <th style="color: var(--text-muted); font-size: 0.75rem;">Usia</th>
                                    <th style="color: var(--text-muted); font-size: 0.75rem;">BB (kg)</th>
                                    <th style="color: var(--text-muted); font-size: 0.75rem;">TB (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailBalita->pengukuran as $ukur)
                                    <tr>
                                        <td>{{ $ukur->tanggal_ukur->format('d/m/Y') }}</td>
                                        <td>{{ $ukur->usia_bulan }} bln</td>
                                        <td>{{ $ukur->berat_badan }}</td>
                                        <td>{{ $ukur->tinggi_badan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-admin.empty-state
                        icon="fas fa-ruler"
                        title="Belum ada pengukuran"
                        description="Data pengukuran balita ini belum tersedia."
                        size="sm"
                    />
                @endif

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <x-admin.button type="button" variant="outline" wire:click="closeDetailModal">
                        Tutup
                    </x-admin.button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <x-admin.confirm-modal
        :show="$showDeleteModal"
        title="Hapus Balita"
        message="Apakah Anda yakin ingin menghapus data balita ini? Tindakan ini tidak dapat dibatalkan."
        confirm-text="Hapus"
        cancel-text="Batal"
        on-confirm="delete"
        on-cancel="cancelDelete"
        variant="danger"
        icon="fas fa-exclamation-triangle"
    />
</div>
