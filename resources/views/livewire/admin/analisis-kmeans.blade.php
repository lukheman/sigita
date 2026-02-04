<div>
    {{-- Page Header --}}
    <x-admin.page-header title="Analisis K-Means Clustering"
        subtitle="Analisis status gizi balita menggunakan algoritma K-Means">
        <x-slot:actions>
            <x-admin.button variant="primary" icon="fas fa-play" wire:click="openModal">
                Jalankan Analisis Baru
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Flash Messages --}}
    @if (session('success'))
        <x-admin.alert variant="success" title="Berhasil!" class="mb-4">
            {{ session('success') }}
        </x-admin.alert>
    @endif

    {{-- Info Card --}}
    <x-admin.alert variant="info" class="mb-4">
        <strong>Tentang K-Means Clustering:</strong> Algoritma ini mengelompokkan data pengukuran balita ke dalam 3
        kategori
        berdasarkan berat badan, tinggi badan, dan usia: <strong>Normal</strong>, <strong>Pendek (Stunted)</strong>,
        dan <strong>Sangat Pendek (Severely Stunted)</strong>.
    </x-admin.alert>

    {{-- Riwayat Analisis --}}
    <div class="modern-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">
                <i class="fas fa-history me-2" style="color: var(--primary-color);"></i>
                Riwayat Analisis
            </h5>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text" style="background: var(--input-bg); border-color: var(--border-color);">
                    <i class="fas fa-search" style="color: var(--text-muted);"></i>
                </span>
                <input type="text" class="form-control" placeholder="Cari judul..."
                    wire:model.live.debounce.300ms="search" style="border-left: none;">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Judul Analisis</th>
                        <th>Cluster</th>
                        <th>Total Data</th>
                        <th>Diproses Oleh</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayatAnalisis as $index => $analisis)
                        <tr wire:key="analisis-{{ $analisis->id }}">
                            <td style="color: var(--text-secondary);">{{ $riwayatAnalisis->firstItem() + $index }}</td>
                            <td style="color: var(--text-secondary);">{{ $analisis->tanggal_proses->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="fw-semibold" style="color: var(--text-primary);">{{ $analisis->judul }}</div>
                            </td>
                            <td>
                                <x-admin.badge variant="primary">{{ $analisis->jumlah_cluster }} Cluster</x-admin.badge>
                            </td>
                            <td style="color: var(--text-primary); font-weight: 500;">{{ $analisis->total_data }} data</td>
                            <td style="color: var(--text-secondary);">{{ $analisis->user->name ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="action-btn" style="color: var(--success-color);"
                                        wire:click="viewResult({{ $analisis->id }})" title="Lihat hasil">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn action-btn-delete"
                                        wire:click="confirmDelete({{ $analisis->id }})" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <x-admin.empty-state icon="fas fa-chart-pie" title="Belum ada riwayat analisis"
                                    description="Jalankan analisis K-Means untuk mulai mengelompokkan data." size="sm" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($riwayatAnalisis->hasPages())
            <div class="d-flex justify-content-end mt-4">
                {{ $riwayatAnalisis->links() }}
            </div>
        @endif
    </div>

    {{-- Modal: Jalankan Analisis Baru --}}
    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" style="max-width: 500px;" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        <i class="fas fa-play me-2" style="color: var(--primary-color);"></i>
                        Jalankan Analisis K-Means
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal" @if($isProcessing) disabled
                    @endif>
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errorMessage)
                    <x-admin.alert variant="danger" class="mb-3">
                        {{ $errorMessage }}
                    </x-admin.alert>
                @endif

                <form wire:submit="runAnalysis">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Analisis</label>
                        <input type="text" class="form-control" id="judul" wire:model="judul"
                            placeholder="Kosongkan untuk judul otomatis" @if($isProcessing) disabled @endif>
                        <small class="text-muted">Contoh: Analisis Stunting Januari 2026</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="filterBulan" class="form-label">Bulan <span
                                    style="color: var(--danger-color);">*</span></label>
                            <select class="form-select" id="filterBulan" wire:model="filterBulan" @if($isProcessing)
                            disabled @endif>
                                @foreach($bulanOptions as $num => $nama)
                                    <option value="{{ $num }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="filterTahun" class="form-label">Tahun <span
                                    style="color: var(--danger-color);">*</span></label>
                            <select class="form-select" id="filterTahun" wire:model="filterTahun" @if($isProcessing)
                            disabled @endif>
                                @foreach($tahunOptions as $tahun)
                                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="filterDesa" class="form-label">Filter Desa (Opsional)</label>
                        <select class="form-select" id="filterDesa" wire:model="filterDesa" @if($isProcessing) disabled
                        @endif>
                            <option value="">Semua Desa</option>
                            @foreach($desaOptions as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="jumlahCluster" class="form-label">Jumlah Cluster <span
                                style="color: var(--danger-color);">*</span></label>
                        <select class="form-select" id="jumlahCluster" wire:model="jumlahCluster" @if($isProcessing)
                        disabled @endif>
                            <option value="2">2 Cluster</option>
                            <option value="3">3 Cluster (Rekomendasi)</option>
                            <option value="4">4 Cluster</option>
                            <option value="5">5 Cluster</option>
                        </select>
                        <small class="text-muted">3 cluster direkomendasikan untuk kategori: Normal, Pendek, Sangat
                            Pendek</small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <x-admin.button type="button" variant="outline" wire:click="closeModal" :disabled="$isProcessing">
                            Batal
                        </x-admin.button>
                        <x-admin.button type="submit" variant="primary" :disabled="$isProcessing">
                            @if($isProcessing)
                                <i class="fas fa-spinner fa-spin me-2"></i> Memproses...
                            @else
                                <i class="fas fa-play me-2"></i> Jalankan Analisis
                            @endif
                        </x-admin.button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Hasil Analisis --}}
    @if ($showResultModal && $selectedPeriode)
        <div class="modal-backdrop-custom" wire:click.self="closeResultModal">
            <div class="modal-content-custom" style="max-width: 900px; max-height: 90vh; overflow-y: auto;" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        <i class="fas fa-chart-pie me-2" style="color: var(--success-color);"></i>
                        Hasil Analisis: {{ $selectedPeriode->judul }}
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeResultModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Info Analisis --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="text-center p-3" style="background: var(--bg-tertiary); border-radius: 12px;">
                            <small class="text-muted d-block">Tanggal Proses</small>
                            <strong
                                style="color: var(--text-primary);">{{ $selectedPeriode->tanggal_proses->format('d/m/Y') }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3" style="background: var(--bg-tertiary); border-radius: 12px;">
                            <small class="text-muted d-block">Jumlah Cluster</small>
                            <strong style="color: var(--text-primary);">{{ $selectedPeriode->jumlah_cluster }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3" style="background: var(--bg-tertiary); border-radius: 12px;">
                            <small class="text-muted d-block">Total Data</small>
                            <strong style="color: var(--text-primary);">{{ $selectedPeriode->total_data }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3" style="background: var(--bg-tertiary); border-radius: 12px;">
                            <small class="text-muted d-block">Diproses Oleh</small>
                            <strong style="color: var(--text-primary);">{{ $selectedPeriode->user->name ?? '-' }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Distribusi Cluster --}}
                <h6 class="mb-3" style="color: var(--text-primary);">Distribusi Cluster</h6>
                <div class="row g-3 mb-4">
                    @php
                        $distribusi = $selectedPeriode->getDistribusiCluster();
                    @endphp
                    @foreach($distribusi as $cluster => $count)
                        @php
                            $color = \App\Services\KMeansService::getClusterColor($cluster);
                            $label = \App\Services\KMeansService::getClusterLabel($cluster);
                            $percentage = $selectedPeriode->total_data > 0 ? round(($count / $selectedPeriode->total_data) * 100, 1) : 0;
                        @endphp
                        <div class="col-md-4">
                            <div class="p-3"
                                style="background: var(--bg-tertiary); border-radius: 12px; border-left: 4px solid var(--{{ $color }}-color);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span style="color: var(--text-secondary);">{{ $label }}</span>
                                    <x-admin.badge :variant="$color">{{ $count }} data</x-admin.badge>
                                </div>
                                <div class="progress" style="height: 6px; background: var(--bg-primary);">
                                    <div class="progress-bar"
                                        style="width: {{ $percentage }}%; background: var(--{{ $color }}-color);"></div>
                                </div>
                                <small class="text-muted">{{ $percentage }}%</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Scatter Chart (Diagram Kartesius) --}}
                <h6 class="mb-3" style="color: var(--text-primary);">
                    <i class="fas fa-chart-scatter me-2"></i>Grafik Scatter Plot (TB vs BB)
                </h6>
                @php
                    $chartData = $selectedPeriode->hasilCluster->map(function($h) {
                        return [
                            'x' => (float) $h->pengukuran->tinggi_badan,
                            'y' => (float) $h->pengukuran->berat_badan,
                            'cluster' => (int) $h->cluster,
                            'nama' => $h->pengukuran->balita->nama_lengkap ?? '-'
                        ];
                    })->values()->toArray();
                    $centroidsData = $selectedPeriode->data_centroid ?? [];
                @endphp
                <div class="mb-4 p-3" style="background: var(--bg-tertiary); border-radius: 12px;" 
                     wire:ignore
                     x-data
                     x-init="$nextTick(() => { setTimeout(() => initClusterChart(), 200); })">
                    <canvas id="clusterScatterChart" 
                            data-chart='@json($chartData)'
                            data-centroids='@json($centroidsData)'
                            style="max-height: 400px; width: 100%;"></canvas>
                </div>

                {{-- Centroid --}}
                @if($selectedPeriode->data_centroid)
                    <h6 class="mb-3" style="color: var(--text-primary);">Nilai Centroid</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm" style="color: var(--text-primary);">
                            <thead>
                                <tr>
                                    <th>Cluster</th>
                                    <th>JK</th>
                                    <th>Usia (bln)</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>ASI</th>
                                    <th>Air Bersih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedPeriode->data_centroid as $i => $centroid)
                                    <tr>
                                        <td>
                                            <x-admin.badge :variant="\App\Services\KMeansService::getClusterColor($i)">
                                                {{ \App\Services\KMeansService::getClusterLabel($i) }}
                                            </x-admin.badge>
                                        </td>
                                        <td>{{ isset($centroid['jenis_kelamin']) ? ($centroid['jenis_kelamin'] < 0.5 ? 'L' : 'P') : '-' }}</td>
                                        <td>{{ number_format($centroid['usia_bulan'] ?? 0, 1) }}</td>
                                        <td>{{ number_format($centroid['berat_badan'] ?? 0, 2) }}</td>
                                        <td>{{ number_format($centroid['tinggi_badan'] ?? 0, 2) }}</td>
                                        <td>{{ isset($centroid['asi_eksklusif']) ? ($centroid['asi_eksklusif'] >= 0.5 ? 'Ya' : 'Tidak') : '-' }}</td>
                                        <td>{{ isset($centroid['akses_air_bersih']) ? ($centroid['akses_air_bersih'] >= 0.5 ? 'Ya' : 'Tidak') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Ranking Desa --}}
                @php
                    $desaStats = $selectedPeriode->getDesaStatistics();
                @endphp
                @if(count($desaStats) > 0)
                    <h6 class="mb-3" style="color: var(--text-primary);">
                        <i class="fas fa-map-marker-alt me-2"></i>Ranking Desa (Tingkat Masalah Gizi)
                    </h6>
                    <div class="mb-4">
                        <x-admin.alert variant="info" class="mb-3">
                            <small>Desa diurutkan berdasarkan tingkat masalah gizi (stunting + gizi buruk) tertinggi.</small>
                        </x-admin.alert>
                        <div class="table-responsive">
                            <table class="table table-sm table-modern">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Desa</th>
                                        <th>Total</th>
                                        <th>Gizi Baik</th>
                                        <th>Gizi Kurang</th>
                                        <th>Gizi Buruk</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($desaStats as $index => $stat)
                                        <tr @if($index < 3) style="background: rgba(var(--danger-rgb), 0.1);" @endif>
                                            <td>
                                                @if($index === 0)
                                                    <span style="color: var(--danger-color); font-weight: bold;">🔴 1</span>
                                                @elseif($index === 1)
                                                    <span style="color: var(--warning-color); font-weight: bold;">🟠 2</span>
                                                @elseif($index === 2)
                                                    <span style="color: var(--warning-color);">🟡 3</span>
                                                @else
                                                    {{ $index + 1 }}
                                                @endif
                                            </td>
                                            <td style="color: var(--text-primary); font-weight: 500;">{{ $stat['nama_desa'] }}</td>
                                            <td>{{ $stat['total'] }}</td>
                                            <td>
                                                <x-admin.badge variant="success">{{ $stat['cluster_0'] }} ({{ $stat['pct_gizi_baik'] ?? 0 }}%)</x-admin.badge>
                                            </td>
                                            <td>
                                                <x-admin.badge variant="warning">{{ $stat['cluster_1'] }} ({{ $stat['pct_gizi_kurang'] ?? 0 }}%)</x-admin.badge>
                                            </td>
                                            <td>
                                                <x-admin.badge variant="danger">{{ $stat['cluster_2'] }} ({{ $stat['pct_gizi_buruk'] ?? 0 }}%)</x-admin.badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Bar Chart untuk Ranking Desa --}}
                        <h6 class="mt-4 mb-3" style="color: var(--text-primary);">
                            <i class="fas fa-chart-bar me-2"></i>Grafik Status Gizi per Desa
                        </h6>
                        <div class="p-3" style="background: var(--bg-primary); border-radius: 12px;" 
                             wire:ignore
                             x-data
                             x-init="$nextTick(() => { setTimeout(() => initDesaBarChart(), 300); })">
                            <canvas id="desaBarChart" 
                                    data-desa-stats='@json($desaStats)'
                                    style="max-height: 350px; width: 100%;"></canvas>
                        </div>
                    </div>
                @endif

                {{-- Detail Data per Cluster --}}
                <h6 class="mb-3" style="color: var(--text-primary);">Detail Data</h6>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-modern">
                        <thead style="position: sticky; top: 0; background: var(--bg-secondary);">
                            <tr>
                                <th>Nama Balita</th>
                                <th>Desa</th>
                                <th>Usia</th>
                                <th>BB (kg)</th>
                                <th>TB (cm)</th>
                                <th>Cluster</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedPeriode->hasilCluster->sortBy('cluster') as $hasil)
                                <tr>
                                    <td style="color: var(--text-primary);">
                                        {{ $hasil->pengukuran->balita->nama_lengkap ?? '-' }}</td>
                                    <td style="color: var(--text-secondary);">
                                        {{ $hasil->pengukuran->balita->desa->nama_desa ?? '-' }}</td>
                                    <td>{{ $hasil->pengukuran->usia_bulan }} bln</td>
                                    <td>{{ number_format($hasil->pengukuran->berat_badan, 2) }}</td>
                                    <td>{{ number_format($hasil->pengukuran->tinggi_badan, 2) }}</td>
                                    <td>
                                        <x-admin.badge :variant="\App\Services\KMeansService::getClusterColor($hasil->cluster)">
                                            {{ \App\Services\KMeansService::getClusterLabel($hasil->cluster) }}
                                        </x-admin.badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <x-admin.button type="button" variant="outline" wire:click="closeResultModal">
                        Tutup
                    </x-admin.button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <x-admin.confirm-modal :show="$showDeleteModal" title="Hapus Analisis"
        message="Apakah Anda yakin ingin menghapus data analisis ini beserta semua hasil cluster-nya?"
        confirm-text="Hapus" cancel-text="Batal" on-confirm="delete" on-cancel="cancelDelete" variant="danger"
        icon="fas fa-exclamation-triangle" />

    {{-- Chart Initialization Script --}}
    <script>
        function initClusterChart() {
            const canvas = document.getElementById('clusterScatterChart');
            if (!canvas) return;
            
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }
            
            // Get data from data attributes
            let chartData, centroidsData;
            try {
                chartData = JSON.parse(canvas.dataset.chart || '[]');
                centroidsData = JSON.parse(canvas.dataset.centroids || '[]');
            } catch(e) {
                console.error('Error parsing chart data:', e);
                return;
            }
            
            // Destroy existing chart if any
            if (window.clusterChart && typeof window.clusterChart.destroy === 'function') {
                window.clusterChart.destroy();
            }
            
            const ctx = canvas.getContext('2d');
            
            // Colors for clusters
            const colors = {
                0: { bg: 'rgba(40, 167, 69, 0.6)', border: 'rgb(40, 167, 69)' },
                1: { bg: 'rgba(255, 193, 7, 0.6)', border: 'rgb(255, 193, 7)' },
                2: { bg: 'rgba(220, 53, 69, 0.6)', border: 'rgb(220, 53, 69)' }
            };
            
            const clusterLabels = {
                0: 'Gizi Baik',
                1: 'Gizi Kurang', 
                2: 'Gizi Buruk'
            };
            
            // Group data by cluster
            const datasets = [];
            
            // Add data points for each cluster
            for (let i = 0; i <= 2; i++) {
                const points = chartData.filter(d => d.cluster === i);
                if (points.length > 0) {
                    datasets.push({
                        label: clusterLabels[i] || `Cluster ${i}`,
                        data: points.map(p => ({ x: p.x, y: p.y, nama: p.nama })),
                        backgroundColor: colors[i]?.bg || 'rgba(100, 100, 100, 0.6)',
                        borderColor: colors[i]?.border || 'rgb(100, 100, 100)',
                        borderWidth: 1,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    });
                }
            }
            
            // Add centroids as special markers
            if (centroidsData && centroidsData.length > 0) {
                const centroidPoints = centroidsData.map((c, i) => ({
                    x: c.tinggi_badan || 0,
                    y: c.berat_badan || 0,
                    cluster: i
                }));
                
                datasets.push({
                    label: 'Centroid',
                    data: centroidPoints.map(c => ({ x: c.x, y: c.y })),
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    borderColor: '#fff',
                    borderWidth: 2,
                    pointRadius: 12,
                    pointHoverRadius: 14,
                    pointStyle: 'crossRot'
                });
            }
            
            window.clusterChart = new Chart(ctx, {
                type: 'scatter',
                data: { datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Visualisasi Cluster (Tinggi Badan vs Berat Badan)',
                            color: '#333'
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#666',
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const point = context.raw;
                                    let label = context.dataset.label || '';
                                    if (point.nama) {
                                        label += `: ${point.nama}`;
                                    }
                                    label += ` (TB: ${point.x} cm, BB: ${point.y} kg)`;
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Tinggi Badan (cm)',
                                color: '#666'
                            },
                            grid: {
                                color: 'rgba(128, 128, 128, 0.2)'
                            },
                            ticks: {
                                color: '#888'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Berat Badan (kg)',
                                color: '#666'
                            },
                            grid: {
                                color: 'rgba(128, 128, 128, 0.2)'
                            },
                            ticks: {
                                color: '#888'
                            }
                        }
                    }
                }
            });
        }
        
        // Bar Chart for Desa Ranking
        function initDesaBarChart() {
            const canvas = document.getElementById('desaBarChart');
            if (!canvas) return;
            
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }
            
            let desaStats;
            try {
                desaStats = JSON.parse(canvas.dataset.desaStats || '[]');
            } catch(e) {
                console.error('Error parsing desa stats:', e);
                return;
            }
            
            if (!desaStats || desaStats.length === 0) return;
            
            // Destroy existing chart if any
            if (window.desaBarChartInstance && typeof window.desaBarChartInstance.destroy === 'function') {
                window.desaBarChartInstance.destroy();
            }
            
            const ctx = canvas.getContext('2d');
            
            // Prepare data
            const labels = desaStats.map(d => d.nama_desa);
            const giziBaik = desaStats.map(d => d.cluster_0 || 0);
            const giziKurang = desaStats.map(d => d.cluster_1 || 0);
            const giziBuruk = desaStats.map(d => d.cluster_2 || 0);
            
            window.desaBarChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Gizi Baik',
                            data: giziBaik,
                            backgroundColor: 'rgba(40, 167, 69, 0.8)',
                            borderColor: 'rgb(40, 167, 69)',
                            borderWidth: 1
                        },
                        {
                            label: 'Gizi Kurang',
                            data: giziKurang,
                            backgroundColor: 'rgba(255, 193, 7, 0.8)',
                            borderColor: 'rgb(255, 193, 7)',
                            borderWidth: 1
                        },
                        {
                            label: 'Gizi Buruk',
                            data: giziBuruk,
                            backgroundColor: 'rgba(220, 53, 69, 0.8)',
                            borderColor: 'rgb(220, 53, 69)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribusi Status Gizi per Desa',
                            color: '#333',
                            font: { size: 14 }
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#666',
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                afterTitle: function(context) {
                                    const idx = context[0].dataIndex;
                                    const total = desaStats[idx]?.total || 0;
                                    return `Total: ${total} balita`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Desa',
                                color: '#666'
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#888',
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Jumlah Balita',
                                color: '#666'
                            },
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(128, 128, 128, 0.2)'
                            },
                            ticks: {
                                color: '#888',
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
        
        // Initialize all charts
        function initAllCharts() {
            initClusterChart();
            initDesaBarChart();
        }
        
        // Initialize on Livewire component updates
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', () => {
                setTimeout(initAllCharts, 100);
            });
        });
        
        // Also try on page load and navigation
        document.addEventListener('DOMContentLoaded', () => setTimeout(initAllCharts, 500));
        document.addEventListener('livewire:navigated', () => setTimeout(initAllCharts, 500));
    </script>
</div>