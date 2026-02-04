<div>
    {{-- Page Header --}}
    <x-admin.page-header title="Dashboard" subtitle="Selamat datang di SIGITA - Sistem Informasi Gizi Balita">
        <x-slot:actions>
            <x-admin.button variant="primary" icon="fas fa-sync-alt" wire:click="$refresh">
                Refresh Data
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <x-admin.stat-card icon="fas fa-baby" label="Total Balita" :value="$totalBalita" variant="primary" />
        </div>
        <div class="col-md-6 col-lg-3">
            <x-admin.stat-card icon="fas fa-map-marker-alt" label="Total Desa" :value="$totalDesa"
                variant="secondary" />
        </div>
        <div class="col-md-6 col-lg-3">
            <x-admin.stat-card icon="fas fa-ruler" label="Pengukuran Bulan Ini" :value="$pengukuranBulanIni"
                :trend-value="abs($pengukuranTrend) . '% dari bulan lalu'"
                :trend-direction="$pengukuranTrend >= 0 ? 'up' : 'down'" variant="success" />
        </div>
        <div class="col-md-6 col-lg-3">
            <x-admin.stat-card icon="fas fa-user-nurse" label="Total Petugas" :value="$totalPetugas"
                variant="warning" />
        </div>
    </div>

    {{-- Second Row: Gender Distribution & Age Distribution --}}
    <div class="row g-4 mb-4">
        {{-- Gender Distribution --}}
        <div class="col-md-6 col-lg-4">
            <div class="modern-card h-100">
                <h5 class="mb-4" style="color: var(--text-primary); font-weight: 600;">
                    <i class="fas fa-venus-mars me-2" style="color: var(--primary-color);"></i>
                    Distribusi Jenis Kelamin
                </h5>

                <div class="d-flex justify-content-around align-items-center mb-4">
                    <div class="text-center">
                        <div class="stat-icon mx-auto mb-2"
                            style="background: rgba(99, 102, 241, 0.1); color: var(--primary-color); width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class="fas fa-male"></i>
                        </div>
                        <h3 class="mb-0" style="color: var(--text-primary);">{{ $balitaLakiLaki }}</h3>
                        <small class="text-muted">Laki-laki</small>
                    </div>
                    <div class="text-center">
                        <div class="stat-icon mx-auto mb-2"
                            style="background: rgba(239, 68, 68, 0.1); color: var(--danger-color); width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class="fas fa-female"></i>
                        </div>
                        <h3 class="mb-0" style="color: var(--text-primary);">{{ $balitaPerempuan }}</h3>
                        <small class="text-muted">Perempuan</small>
                    </div>
                </div>

                @if($totalBalita > 0)
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small style="color: var(--text-secondary);">Laki-laki</small>
                            <small
                                style="color: var(--text-secondary);">{{ round(($balitaLakiLaki / $totalBalita) * 100, 1) }}%</small>
                        </div>
                        <div class="progress" style="height: 8px; background: var(--bg-tertiary);">
                            <div class="progress-bar"
                                style="width: {{ ($balitaLakiLaki / $totalBalita) * 100 }}%; background: var(--primary-color);">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-1 mt-3">
                            <small style="color: var(--text-secondary);">Perempuan</small>
                            <small
                                style="color: var(--text-secondary);">{{ round(($balitaPerempuan / $totalBalita) * 100, 1) }}%</small>
                        </div>
                        <div class="progress" style="height: 8px; background: var(--bg-tertiary);">
                            <div class="progress-bar"
                                style="width: {{ ($balitaPerempuan / $totalBalita) * 100 }}%; background: var(--danger-color);">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Age Distribution --}}
        <div class="col-md-6 col-lg-4">
            <div class="modern-card h-100">
                <h5 class="mb-4" style="color: var(--text-primary); font-weight: 600;">
                    <i class="fas fa-birthday-cake me-2" style="color: var(--warning-color);"></i>
                    Distribusi Usia
                </h5>

                @foreach($ageDistribution as $range => $count)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small style="color: var(--text-secondary);">{{ $range }}</small>
                            <small style="color: var(--text-primary); font-weight: 600;">{{ $count }} balita</small>
                        </div>
                        <div class="progress" style="height: 6px; background: var(--bg-tertiary);">
                            <div class="progress-bar"
                                style="width: {{ $totalBalita > 0 ? ($count / $totalBalita) * 100 : 0 }}%; background: var(--success-color);">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Balita per Desa --}}
        <div class="col-md-12 col-lg-4">
            <div class="modern-card h-100">
                <h5 class="mb-4" style="color: var(--text-primary); font-weight: 600;">
                    <i class="fas fa-map me-2" style="color: var(--secondary-color);"></i>
                    Top 5 Desa (Jumlah Balita)
                </h5>

                @forelse($balitaPerDesa as $desa)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}"
                        style="border-color: var(--border-color) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill"
                                style="background: var(--primary-color); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                {{ $loop->iteration }}
                            </span>
                            <span style="color: var(--text-primary);">{{ $desa->nama_desa }}</span>
                        </div>
                        <x-admin.badge variant="primary">{{ $desa->balita_count }} Balita</x-admin.badge>
                    </div>
                @empty
                    <x-admin.empty-state icon="fas fa-map-marker-alt" title="Belum ada data desa" size="sm" />
                @endforelse
            </div>
        </div>
    </div>

    {{-- Third Row: Latest Pengukuran & Quick Actions --}}
    <div class="row g-4">
        {{-- Latest Pengukuran --}}
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">
                        <i class="fas fa-clock me-2" style="color: var(--success-color);"></i>
                        Pengukuran Terbaru
                    </h5>
                    <a href="{{ route('admin.pengukuran') }}" class="btn btn-sm"
                        style="color: var(--primary-color); background: rgba(99, 102, 241, 0.1); border: none; border-radius: 8px;">
                        Lihat Semua
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Balita</th>
                                <th>Desa</th>
                                <th>Usia</th>
                                <th>BB (kg)</th>
                                <th>TB (cm)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestPengukuran as $ukur)
                                <tr>
                                    <td style="color: var(--text-secondary);">{{ $ukur->tanggal_ukur->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <span class="fw-semibold"
                                            style="color: var(--text-primary);">{{ $ukur->balita->nama_lengkap }}</span>
                                    </td>
                                    <td style="color: var(--text-secondary);">{{ $ukur->balita->desa->nama_desa }}</td>
                                    <td>
                                        <x-admin.badge variant="info">{{ $ukur->usia_bulan }} bln</x-admin.badge>
                                    </td>
                                    <td style="color: var(--text-primary); font-weight: 500;">
                                        {{ number_format($ukur->berat_badan, 2) }}</td>
                                    <td style="color: var(--text-primary); font-weight: 500;">
                                        {{ number_format($ukur->tinggi_badan, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <x-admin.empty-state icon="fas fa-ruler" title="Belum ada data pengukuran"
                                            description="Data pengukuran akan muncul di sini." size="sm" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-4">
            <div class="modern-card h-100">
                <h5 class="mb-4" style="color: var(--text-primary); font-weight: 600;">
                    <i class="fas fa-bolt me-2" style="color: var(--warning-color);"></i>
                    Aksi Cepat
                </h5>

                <div class="d-grid gap-3">
                    <a href="{{ route('admin.balita') }}" class="quick-action-btn">
                        <div class="quick-action-icon"
                            style="background: rgba(99, 102, 241, 0.1); color: var(--primary-color);">
                            <i class="fas fa-baby"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="color: var(--text-primary);">Tambah Balita</div>
                            <small class="text-muted">Daftarkan balita baru</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>

                    <a href="{{ route('admin.pengukuran') }}" class="quick-action-btn">
                        <div class="quick-action-icon"
                            style="background: rgba(16, 185, 129, 0.1); color: var(--success-color);">
                            <i class="fas fa-ruler"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="color: var(--text-primary);">Input Pengukuran</div>
                            <small class="text-muted">Catat data pengukuran</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>

                    <a href="{{ route('admin.desa') }}" class="quick-action-btn">
                        <div class="quick-action-icon"
                            style="background: rgba(14, 165, 233, 0.1); color: var(--secondary-color);">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="color: var(--text-primary);">Kelola Desa</div>
                            <small class="text-muted">Atur data wilayah</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>

                    <a href="{{ route('admin.users') }}" class="quick-action-btn">
                        <div class="quick-action-icon"
                            style="background: rgba(245, 158, 11, 0.1); color: var(--warning-color);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="color: var(--text-primary);">Kelola Pengguna</div>
                            <small class="text-muted">Atur akun petugas</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Latest Analisis Info --}}
    @if($latestAnalisis)
        <div class="row g-4 mt-2">
            <div class="col-12">
                <x-admin.alert variant="info" title="Analisis Terakhir">
                    <strong>{{ $latestAnalisis->judul }}</strong> -
                    Diproses pada {{ $latestAnalisis->tanggal_proses->format('d F Y') }}
                    oleh {{ $latestAnalisis->user->name ?? 'Unknown' }}.
                    Total {{ $latestAnalisis->total_data }} data dianalisis menggunakan
                    {{ $latestAnalisis->jumlah_cluster }} cluster.
                </x-admin.alert>
            </div>
        </div>
    @endif

    <style>
        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--bg-tertiary);
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .quick-action-btn:hover {
            background: var(--bg-primary);
            transform: translateX(5px);
        }

        .quick-action-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .quick-action-btn>div:nth-child(2) {
            flex: 1;
        }

        .stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }
    </style>
</div>