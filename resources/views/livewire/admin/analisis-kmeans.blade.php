<div>
    <x-admin.page-header title="Analisis K-Means Clustering"
        subtitle="Pemetaan risiko gizi per desa dari data rekap agregat">
        <x-slot:actions>
            <x-admin.button variant="primary" icon="fas fa-play" wire:click="openModal">
                Jalankan Analisis Baru
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <x-admin.alert variant="success" title="Berhasil!" class="mb-4">
            {{ session('success') }}
        </x-admin.alert>
    @endif

    <x-admin.alert variant="info" class="mb-4">
        <strong>K-Means agregat desa:</strong> setiap desa menjadi satu titik data dengan fitur
        cakupan penimbangan, % stunting, % gizi kurang, dan % BB kurang.
        Hasil berupa label <strong>Risiko Rendah / Sedang / Tinggi</strong> — bukan diagnosis medis.
    </x-admin.alert>

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
                        <th>Periode Data</th>
                        <th>Cluster</th>
                        <th>Total Desa</th>
                        <th>Diproses Oleh</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayatAnalisis as $index => $analisis)
                        <tr wire:key="analisis-{{ $analisis->id }}">
                            <td style="color: var(--text-secondary);">{{ $riwayatAnalisis->firstItem() + $index }}</td>
                            <td style="color: var(--text-secondary);">{{ $analisis->tanggal_proses->format('d/m/Y H:i') }}</td>
                            <td><div class="fw-semibold" style="color: var(--text-primary);">{{ $analisis->judul }}</div></td>
                            <td><x-admin.badge variant="info">{{ $analisis->periode_label }}</x-admin.badge></td>
                            <td><x-admin.badge variant="primary">{{ $analisis->jumlah_cluster }} Cluster</x-admin.badge></td>
                            <td style="color: var(--text-primary); font-weight: 500;">{{ $analisis->total_data }} desa</td>
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
                            <td colspan="8" class="text-center py-4">
                                <x-admin.empty-state icon="fas fa-chart-pie" title="Belum ada riwayat analisis"
                                    description="Jalankan analisis K-Means untuk mulai memetakan desa." size="sm" />
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

    @if ($showModal)
        <div class="modal-backdrop-custom" wire:click.self="closeModal">
            <div class="modal-content-custom" style="max-width: 500px;" wire:click.stop>
                <div class="modal-header-custom">
                    <h5 class="modal-title-custom">
                        <i class="fas fa-play me-2" style="color: var(--primary-color);"></i>
                        Jalankan Analisis K-Means
                    </h5>
                    <button type="button" class="modal-close-btn" wire:click="closeModal" @if($isProcessing) disabled @endif>
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errorMessage)
                    <x-admin.alert variant="danger" class="mb-3">{{ $errorMessage }}</x-admin.alert>
                @endif

                <form wire:submit="runAnalysis">
                    <div class="mb-3">
                        <label class="form-label">Judul Analisis</label>
                        <input type="text" class="form-control" wire:model="judul"
                            placeholder="Kosongkan untuk judul otomatis" @if($isProcessing) disabled @endif>
                        <small class="text-muted">Contoh: Analisis Risiko Gizi Januari 2026</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Periode Data <span style="color: var(--danger-color);">*</span></label>
                        <select class="form-select" wire:model="periode" @if($isProcessing) disabled @endif>
                            @forelse($periodeOptions as $val => $label)
                                <option value="{{ $val }}">{{ \App\Models\RekapGiziDesa::formatPeriode($label) }}</option>
                            @empty
                                <option value="{{ $periode }}">{{ \App\Models\RekapGiziDesa::formatPeriode($periode) }}</option>
                            @endforelse
                        </select>
                        <small class="text-muted">Satu titik data = satu desa pada periode ini. Desa dengan indikator NULL dilewati.</small>
                        @error('periode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Jumlah Cluster <span style="color: var(--danger-color);">*</span></label>
                        <select class="form-select" wire:model="jumlahCluster" @if($isProcessing) disabled @endif>
                            <option value="2">2 Cluster</option>
                            <option value="3">3 Cluster (Rekomendasi: Rendah/Sedang/Tinggi)</option>
                            <option value="4">4 Cluster</option>
                            <option value="5">5 Cluster</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <x-admin.button type="button" variant="outline" wire:click="closeModal" :disabled="$isProcessing">Batal</x-admin.button>
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

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="text-center p-3" style="background: var(--bg-tertiary); border-radius: 12px;">
                            <small class="text-muted d-block">Periode Data</small>
                            <strong style="color: var(--text-primary);">{{ $selectedPeriode->periode_label }}</strong>
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
                            <small class="text-muted d-block">Total Desa</small>
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

                @if(count($skippedDesa) > 0)
                    <x-admin.alert variant="warning" class="mb-4">
                        <strong>{{ count($skippedDesa) }} desa dilewati</strong> karena data belum lengkap:
                        {{ collect($skippedDesa)->pluck('nama_desa')->join(', ') }}
                    </x-admin.alert>
                @endif

                <h6 class="mb-3" style="color: var(--text-primary);">Distribusi Cluster</h6>
                <div class="row g-3 mb-4">
                    @php $distribusi = $selectedPeriode->getDistribusiCluster(); @endphp
                    @foreach($distribusi as $cluster => $count)
                        @php
                            $color = \App\Services\KMeansService::getClusterColor($cluster);
                            $label = \App\Services\KMeansService::getClusterLabel($cluster);
                            $percentage = $selectedPeriode->total_data > 0 ? round(($count / $selectedPeriode->total_data) * 100, 1) : 0;
                        @endphp
                        <div class="col-md-4">
                            <div class="p-3" style="background: var(--bg-tertiary); border-radius: 12px; border-left: 4px solid var(--{{ $color }}-color);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span style="color: var(--text-secondary);">{{ $label }}</span>
                                    <x-admin.badge :variant="$color">{{ $count }} desa</x-admin.badge>
                                </div>
                                <div class="progress" style="height: 6px; background: var(--bg-primary);">
                                    <div class="progress-bar" style="width: {{ $percentage }}%; background: var(--{{ $color }}-color);"></div>
                                </div>
                                <small class="text-muted">{{ $percentage }}%</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <h6 class="mb-3" style="color: var(--text-primary);">
                    <i class="fas fa-chart-scatter me-2"></i>Scatter Plot (% Stunting vs % Gizi Kurang)
                </h6>
                @php
                    $chartData = $selectedPeriode->hasilCluster->map(function($h) {
                        return [
                            'x' => (float) ($h->rekap->pct_stunting ?? 0),
                            'y' => (float) ($h->rekap->pct_gizi_kurang ?? 0),
                            'cluster' => (int) $h->cluster,
                            'nama' => $h->rekap->desa->nama_desa ?? '-',
                        ];
                    })->values()->toArray();
                    $centroidsData = $selectedPeriode->data_centroid ?? [];
                @endphp
                <div class="mb-4 p-3" style="background: var(--bg-tertiary); border-radius: 12px;"
                     wire:ignore x-data x-init="$nextTick(() => { setTimeout(() => initClusterChart(), 200); })">
                    <canvas id="clusterScatterChart" data-chart='@json($chartData)' data-centroids='@json($centroidsData)' style="max-height: 400px; width: 100%;"></canvas>
                </div>

                @if($selectedPeriode->data_centroid)
                    <h6 class="mb-3" style="color: var(--text-primary);">Nilai Centroid (% — satuan asli)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm" style="color: var(--text-primary);">
                            <thead>
                                <tr>
                                    <th>Cluster</th>
                                    <th>Stunting (%)</th>
                                    <th>Gizi Kurang (%)</th>
                                    <th>BB Kurang (%)</th>
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
                                        <td>{{ number_format($centroid['persentase_stunting'] ?? 0, 1) }}</td>
                                        <td>{{ number_format($centroid['persentase_gizi_kurang'] ?? 0, 1) }}</td>
                                        <td>{{ number_format($centroid['persentase_bb_kurang'] ?? 0, 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @php $desaStats = $selectedPeriode->getDesaStatistics(); @endphp
                @if(count($desaStats) > 0)
                    <h6 class="mb-3" style="color: var(--text-primary);">
                        <i class="fas fa-map-marker-alt me-2"></i>Ranking Desa Prioritas
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-modern">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Desa</th>
                                    <th>Balita</th>
                                    <th>Stunting</th>
                                    <th>Gizi Kurang</th>
                                    <th>BB Kurang</th>
                                    <th>Cluster</th>
                                    <th>Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($desaStats as $index => $stat)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td style="font-weight: 500;">{{ $stat['nama_desa'] }}</td>
                                        <td>{{ $stat['jumlah_balita'] }}</td>
                                        <td>{{ $stat['jumlah_stunting'] }}</td>
                                        <td>{{ $stat['jumlah_gizi_kurang'] }}</td>
                                        <td>{{ $stat['jumlah_bb_kurang'] }}</td>
                                        <td>
                                            <x-admin.badge :variant="$stat['kategori_variant']">{{ $stat['kategori_icon'] }} {{ $stat['kategori_desa'] }}</x-admin.badge>
                                        </td>
                                        <td>{{ number_format($stat['skor_risiko'] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mt-4 mb-3" style="color: var(--text-primary);">
                        <i class="fas fa-chart-bar me-2"></i>Grafik % Indikator per Desa
                    </h6>
                    <div class="p-3" style="background: var(--bg-primary); border-radius: 12px;"
                         wire:ignore x-data x-init="$nextTick(() => { setTimeout(() => initDesaBarChart(), 300); })">
                        <canvas id="desaBarChart" data-desa-stats='@json($desaStats)' style="max-height: 350px; width: 100%;"></canvas>
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <x-admin.button type="button" variant="outline" wire:click="closeResultModal">Tutup</x-admin.button>
                </div>
            </div>
        </div>
    @endif

    <x-admin.confirm-modal :show="$showDeleteModal" title="Hapus Analisis"
        message="Apakah Anda yakin ingin menghapus data analisis ini beserta semua hasil cluster-nya?"
        confirm-text="Hapus" cancel-text="Batal" on-confirm="delete" on-cancel="cancelDelete" variant="danger"
        icon="fas fa-exclamation-triangle" />

    <script>
        function initClusterChart() {
            const canvas = document.getElementById('clusterScatterChart');
            if (!canvas || typeof Chart === 'undefined') return;
            let chartData, centroidsData;
            try {
                chartData = JSON.parse(canvas.dataset.chart || '[]');
                centroidsData = JSON.parse(canvas.dataset.centroids || '[]');
            } catch(e) { return; }
            if (window.clusterChart?.destroy) window.clusterChart.destroy();
            const ctx = canvas.getContext('2d');
            const colors = {
                0: { bg: 'rgba(40,167,69,0.6)', border: 'rgb(40,167,69)' },
                1: { bg: 'rgba(255,193,7,0.6)', border: 'rgb(255,193,7)' },
                2: { bg: 'rgba(220,53,69,0.6)', border: 'rgb(220,53,69)' },
                3: { bg: 'rgba(13,110,253,0.6)', border: 'rgb(13,110,253)' },
                4: { bg: 'rgba(111,66,193,0.6)', border: 'rgb(111,66,193)' }
            };
            const labels = { 0: 'Risiko Rendah', 1: 'Risiko Sedang', 2: 'Risiko Tinggi' };
            const datasets = [];
            const maxK = Math.max(2, ...chartData.map(d => d.cluster));
            for (let i = 0; i <= maxK; i++) {
                const points = chartData.filter(d => d.cluster === i);
                if (!points.length) continue;
                datasets.push({
                    label: labels[i] ?? `Cluster ${i}`,
                    data: points.map(p => ({ x: p.x, y: p.y, nama: p.nama })),
                    backgroundColor: colors[i]?.bg, borderColor: colors[i]?.border,
                    borderWidth: 1, pointRadius: 7, pointHoverRadius: 9
                });
            }
            if (centroidsData?.length) {
                datasets.push({
                    label: 'Centroid',
                    data: centroidsData.map(c => ({ x: c.persentase_stunting || 0, y: c.persentase_gizi_kurang || 0 })),
                    backgroundColor: 'rgba(0,0,0,0.8)', borderColor: '#fff',
                    borderWidth: 2, pointRadius: 12, pointHoverRadius: 14, pointStyle: 'crossRot'
                });
            }
            window.clusterChart = new Chart(ctx, {
                type: 'scatter',
                data: { datasets },
                options: {
                    responsive: true,
                    plugins: {
                        title: { display: true, text: 'Pemetaan Desa (% Stunting vs % Gizi Kurang)' },
                        tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${c.raw.nama} (S: ${c.raw.x}%, GK: ${c.raw.y}%)` } }
                    },
                    scales: {
                        x: { title: { display: true, text: '% Stunting' } },
                        y: { title: { display: true, text: '% Gizi Kurang' } }
                    }
                }
            });
        }
        function initDesaBarChart() {
            const canvas = document.getElementById('desaBarChart');
            if (!canvas || typeof Chart === 'undefined') return;
            let stats;
            try { stats = JSON.parse(canvas.dataset.desaStats || '[]'); } catch(e) { return; }
            if (!stats?.length) return;
            if (window.desaBarChartInstance?.destroy) window.desaBarChartInstance.destroy();
            window.desaBarChartInstance = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: stats.map(d => d.nama_desa),
                    datasets: [
                        { label: '% Stunting', data: stats.map(d => d.pct_stunting ?? 0), backgroundColor: 'rgba(220,53,69,0.8)' },
                        { label: '% Gizi Kurang', data: stats.map(d => d.pct_gizi_kurang ?? 0), backgroundColor: 'rgba(255,193,7,0.8)' },
                        { label: '% BB Kurang', data: stats.map(d => d.pct_bb_kurang ?? 0), backgroundColor: 'rgba(13,110,253,0.8)' }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { title: { display: true, text: 'Persentase Indikator per Desa' } },
                    scales: { x: { ticks: { maxRotation: 45, minRotation: 45 } }, y: { beginAtZero: true, title: { display: true, text: '%' } } }
                }
            });
        }
        function initAllCharts() { initClusterChart(); initDesaBarChart(); }
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', () => setTimeout(initAllCharts, 100));
        });
        document.addEventListener('DOMContentLoaded', () => setTimeout(initAllCharts, 500));
        document.addEventListener('livewire:navigated', () => setTimeout(initAllCharts, 500));
    </script>
</div>
