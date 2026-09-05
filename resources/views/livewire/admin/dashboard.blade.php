<div>
    <x-admin.page-header title="Dashboard" subtitle="Rekap agregat gizi per desa — periode {{ \App\Models\RekapGiziDesa::formatPeriode($periode) }}">
        <x-slot:actions>
            <x-admin.button variant="primary" icon="fas fa-sync-alt" wire:click="$refresh">
                Refresh Data
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <x-admin.stat-card icon="fas fa-baby" label="Total Balita" :value="$totalBalita" variant="primary" />
        </div>
        <div class="col-md-6 col-lg-3">
            <x-admin.stat-card icon="fas fa-balance-scale" label="Ditimbang ({{ $cakupan }}%)" :value="$totalDitimbang" variant="success" />
        </div>
        <div class="col-md-6 col-lg-3">
            <x-admin.stat-card icon="fas fa-exclamation-triangle" label="Stunting ({{ $pctStunting }}%)" :value="$totalStunting" variant="danger" />
        </div>
        <div class="col-md-6 col-lg-3">
            <x-admin.stat-card icon="fas fa-map-marker-alt" label="Total Desa" :value="$totalDesa" variant="secondary" />
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="modern-card h-100">
                <h5 class="mb-4" style="font-weight: 600;">Ringkasan {{ \App\Models\RekapGiziDesa::formatPeriode($periode) }}</h5>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><small>Gizi Kurang</small><small class="fw-semibold">{{ $totalGiziKurang }}</small></div>
                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-warning" style="width: {{ $totalDitimbang > 0 ? ($totalGiziKurang / $totalDitimbang) * 100 : 0 }}%;"></div></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><small>BB Kurang</small><small class="fw-semibold">{{ $totalBbKurang }}</small></div>
                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-info" style="width: {{ $totalDitimbang > 0 ? ($totalBbKurang / $totalDitimbang) * 100 : 0 }}%;"></div></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1"><small>Cakupan Penimbangan</small><small class="fw-semibold">{{ $cakupan }}%</small></div>
                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-success" style="width: {{ $cakupan }}%;"></div></div>
                </div>
                <small class="text-muted">Total petugas: {{ $totalPetugas }}</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="modern-card h-100">
                <h5 class="mb-4" style="font-weight: 600;">Top 5 Desa (Stunting)</h5>
                @forelse($topStunting as $i => $r)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill" style="background: var(--primary-color);">{{ $i + 1 }}</span>
                            <span>{{ $r->desa->nama_desa }}</span>
                        </div>
                        <x-admin.badge variant="danger">{{ $r->jumlah_stunting }} kasus</x-admin.badge>
                    </div>
                @empty
                    <x-admin.empty-state title="Belum ada data" size="sm" />
                @endforelse
            </div>
        </div>

        <div class="col-md-12 col-lg-4">
            <div class="modern-card h-100">
                <h5 class="mb-4" style="font-weight: 600;">Desa Belum Lengkap ({{ $belumLengkap->count() }})</h5>
                @forelse($belumLengkap as $r)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>{{ $r->desa->nama_desa }}</span>
                        <x-admin.badge variant="warning">Belum lengkap</x-admin.badge>
                    </div>
                @empty
                    <x-admin.alert variant="success">Semua desa pada periode ini sudah lengkap.</x-admin.alert>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0" style="font-weight: 600;">Rekap Periode {{ \App\Models\RekapGiziDesa::formatPeriode($periode) }}</h5>
                    <a href="{{ route('admin.rekap-gizi') }}" class="btn btn-sm" style="color: var(--primary-color);">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead><tr><th>Desa</th><th>Balita</th><th>Ditimbang</th><th>Stunting</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($latestRekap as $r)
                                <tr>
                                    <td class="fw-semibold">{{ $r->desa->nama_desa }}</td>
                                    <td>{{ $r->jumlah_balita }}</td>
                                    <td>{{ $r->jumlah_ditimbang }}</td>
                                    <td>{{ $r->jumlah_stunting ?? '-' }} @if($r->pct_stunting !== null)<small class="text-muted">({{ $r->pct_stunting }}%)</small>@endif</td>
                                    <td>@if($r->isLengkap())<x-admin.badge variant="success">Lengkap</x-admin.badge>@else<x-admin.badge variant="warning">Belum lengkap</x-admin.badge>@endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4"><x-admin.empty-state title="Belum ada rekap" size="sm" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="modern-card h-100">
                <h5 class="mb-4" style="font-weight: 600;"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                <div class="d-grid gap-3">
                    <a href="{{ route('admin.rekap-gizi') }}" class="quick-action-btn">
                        <div class="quick-action-icon" style="background: rgba(16,185,129,0.1); color: var(--success-color);"><i class="fas fa-clipboard-list"></i></div>
                        <div><div class="fw-semibold">Input Rekap Gizi</div><small class="text-muted">Tambah rekap desa</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('admin.analisis-kmeans') }}" class="quick-action-btn">
                        <div class="quick-action-icon" style="background: rgba(99,102,241,0.1); color: var(--primary-color);"><i class="fas fa-chart-pie"></i></div>
                        <div><div class="fw-semibold">Jalankan K-Means</div><small class="text-muted">Petakan risiko desa</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <a href="{{ route('admin.desa') }}" class="quick-action-btn">
                        <div class="quick-action-icon" style="background: rgba(14,165,233,0.1); color: var(--secondary-color);"><i class="fas fa-map-marker-alt"></i></div>
                        <div><div class="fw-semibold">Kelola Desa</div><small class="text-muted">Atur data wilayah</small></div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($latestAnalisis)
        <div class="row g-4 mt-2">
            <div class="col-12">
                <x-admin.alert variant="info" title="Analisis Terakhir">
                    <strong>{{ $latestAnalisis->judul }}</strong> (periode {{ $latestAnalisis->periode_label }}) —
                    Diproses pada {{ $latestAnalisis->tanggal_proses->format('d F Y') }}
                    oleh {{ $latestAnalisis->user->name ?? 'Unknown' }}.
                    Total {{ $latestAnalisis->total_data }} desa dalam {{ $latestAnalisis->jumlah_cluster }} cluster.
                </x-admin.alert>
            </div>
        </div>
    @endif

    <style>
        .quick-action-btn { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-tertiary); border-radius: 12px; text-decoration: none; transition: all 0.2s ease; }
        .quick-action-btn:hover { background: var(--bg-primary); transform: translateX(5px); }
        .quick-action-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .quick-action-btn>div:nth-child(2) { flex: 1; }
    </style>
</div>
