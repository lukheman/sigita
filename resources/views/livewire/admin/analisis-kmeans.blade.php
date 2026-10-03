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
            <div class="modal-content-custom" style="max-width: 720px; max-height: 90vh; overflow-y: auto;" wire:click.stop>
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

                    <div class="mb-3">
                        <label class="form-label">Mode Analisis <span style="color: var(--danger-color);">*</span></label>
                        <select class="form-select" wire:model.live="modeAnalisis" @if($isProcessing) disabled @endif>
                            <option value="rendah">1 Cluster — Risiko Rendah saja</option>
                            <option value="tinggi">1 Cluster — Risiko Tinggi saja</option>
                            <option value="2cluster">2 Cluster (Rendah / Tinggi)</option>
                        </select>
                        @error('modeAnalisis') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="centroidManualSwitch"
                            wire:model.live="centroidManual" @if($isProcessing) disabled @endif>
                        <label class="form-check-label" for="centroidManualSwitch">Input centroid awal manual (skala Z-score)</label>
                        <div><small class="text-muted">Nilai skala Z-score populasi, boleh negatif (misal 0.25 atau -0.1). Kunci fitur: count mentah (Stunting, Gizi Kurang, BB Kurang, Gizi Lebih, Gizi Baik). Jika mati, centroid awal ditentukan otomatis (KMeans++).</small></div>
                    </div>

                    @if($centroidManual)
                        @php
                            $centroidFieldLabels = \App\Models\RekapGiziDesa::FITUR_LABELS;
                            $centroidCount = $this->modeK();
                        @endphp
                        <div class="row g-3 mb-4">
                            @for($ci = 0; $ci < $centroidCount; $ci++)
                                <div class="{{ $centroidCount > 1 ? 'col-md-6' : 'col-12' }}">
                                    <div class="p-3" style="background: var(--bg-tertiary); border-radius: 12px;">
                                        <strong class="d-block mb-2" style="color: var(--text-primary);">
                                            Centroid Awal C{{ $ci + 1 }}
                                            @if($centroidCount > 1)
                                                <small class="text-muted">({{ $ci === 0 ? 'kandidat Tinggi' : 'kandidat Rendah' }})</small>
                                            @else
                                                <small class="text-muted">({{ $modeAnalisis === 'tinggi' ? 'Risiko Tinggi' : 'Risiko Rendah' }})</small>
                                            @endif
                                        </strong>
                                        @foreach($centroidFieldLabels as $key => $label)
                                            <div class="mb-2">
                                                <label class="form-label small mb-1">{{ str_replace(' (%)', '', $label) }}</label>
                                                <input type="number" step="0.0001" class="form-control form-control-sm"
                                                    wire:model="centroidInputs.{{ $ci }}.{{ $key }}"
                                                    placeholder="misal 0.25" @if($isProcessing) disabled @endif>
                                                @error("centroidInputs.{$ci}.{$key}") <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endfor
                        </div>
                    @endif

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
                            @if($selectedPeriode->jumlah_cluster == 1)
                                @php $singleLabel = \App\Services\KMeansService::getClusterLabel((int) array_key_first($selectedPeriode->getCentroids() ?? [0 => []])); @endphp
                                <div><small class="text-muted">{{ $singleLabel }}</small></div>
                            @endif
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
                            // Label dominan dari kategori tersimpan agar riwayat lama tetap benar
                            $domKategori = $selectedPeriode->hasilCluster()->where('cluster', $cluster)->selectRaw('kategori, COUNT(*) as c')->groupBy('kategori')->orderByDesc('c')->value('kategori');
                            $label = $domKategori ?: \App\Services\KMeansService::getClusterLabel((int) $cluster);
                            $color = \App\Services\KMeansService::getKategoriColor($label);
                            $percentage = $selectedPeriode->total_data > 0 ? round(($count / $selectedPeriode->total_data) * 100, 1) : 0;
                        @endphp
                        <div class="col-md-6">
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

                @php
                    $isZ = $selectedPeriode->isZScore();
                    $fiturLabels = $selectedPeriode->resolveFeatureLabels();
                    $fiturKeys = array_keys($fiturLabels);
                    $minMax = $selectedPeriode->getMinMax();
                    $dataNormalisasi = $selectedPeriode->getDataNormalisasi();
                    $centroidsNormalized = $selectedPeriode->getCentroidsNormalized();
                    $winsorBounds = $selectedPeriode->getWinsorBounds();
                    $meanStd = $selectedPeriode->getMeanStd();
                    $dataWinsorized = $selectedPeriode->getDataWinsorized();
                    $wcss = $selectedPeriode->getWcss();
                    $iterationsHistory = $selectedPeriode->getIterationsHistory();
                    $dataSnapshot = $selectedPeriode->data_snapshot ?? [];
                    // Nama desa outlier per fitur (nilai berubah setelah Winsorization)
                    $outlierNames = [];
                    foreach ($fiturKeys as $fk) {
                        $names = [];
                        foreach ($dataSnapshot as $si => $srow) {
                            $before = (float) ($srow[$fk] ?? 0);
                            $after = (float) ($dataWinsorized[$si][$fk] ?? $before);
                            if (abs($before - $after) > 1e-9) {
                                $names[] = $srow['desa_nama'] ?? ('#'.($si + 1));
                            }
                        }
                        $outlierNames[$fk] = $names;
                    }
                    // Elbow Method per desa = jumlah kuadrat Z-score (untuk K=1 total = WCSS K=1)
                    $elbowPerDesa = [];
                    foreach ($dataNormalisasi as $ni => $nrow) {
                        $s = 0.0;
                        foreach ($fiturKeys as $fk) {
                            $v = (float) ($nrow[$fk] ?? 0);
                            $s += $v * $v;
                        }
                        $elbowPerDesa[$ni] = $s;
                    }
                    // Hasil Akhir dua kolom: Cluster 1 vs Cluster 2 (urutan data asli)
                    $hasilByCluster = [1 => [], 2 => []];
                    foreach (($selectedPeriode->hasilCluster()->with('rekap.desa')->orderBy('id')->get() ?? []) as $hrow) {
                        $lab = (int) ($hrow->cluster ?? 0);
                        if (! isset($hasilByCluster[$lab])) {
                            $hasilByCluster[$lab] = [];
                        }
                        $hasilByCluster[$lab][] = $hrow->rekap->desa->nama_desa ?? ($hrow->rekap->desa_nama ?? '-');
                    }
                    $maxHasilRows = max(count($hasilByCluster[1] ?? []), count($hasilByCluster[2] ?? []));
                @endphp

                @if($isZ)
                    <h6 class="mb-3" style="color: var(--text-primary);">
                        <i class="fas fa-database me-2"></i>Data (count mentah)
                    </h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-modern">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Desa</th>
                                    @foreach($fiturLabels as $label)
                                        <th>{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataSnapshot as $si => $srow)
                                    <tr>
                                        <td>{{ $si + 1 }}</td>
                                        <td style="font-weight: 500;">{{ $srow['desa_nama'] }}</td>
                                        @foreach($fiturKeys as $key)
                                            <td>{{ number_format($srow[$key] ?? 0, 0) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mb-3" style="color: var(--text-primary);">
                        <i class="fas fa-table me-2"></i>Data Winsorization
                    </h6>
                    <x-admin.alert variant="info" class="mb-3">
                        <code>IQR=Q3−Q1</code>, <code>Bawah=Q1−1.5·IQR</code>, <code>Atas=Q3+1.5·IQR</code> (kuantil linear / PERCENTILE.INC).
                        Nilai di luar batas di-clip ke batas. Q1/Q3 dihitung per fitur dari count mentah.
                    </x-admin.alert>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm" style="color: var(--text-primary);">
                            <thead>
                                <tr>
                                    <th>Fitur</th>
                                    <th>Q1</th>
                                    <th>Q3</th>
                                    <th>IQR</th>
                                    <th>Batas Bawah</th>
                                    <th>Batas Atas</th>
                                    <th>Outlier</th>
                                    <th>Desa Outlier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fiturLabels as $key => $label)
                                    @php $b = $winsorBounds[$key] ?? []; @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>{{ number_format($b['q1'] ?? 0, 2) }}</td>
                                        <td>{{ number_format($b['q3'] ?? 0, 2) }}</td>
                                        <td>{{ number_format($b['iqr'] ?? 0, 2) }}</td>
                                        <td>{{ number_format($b['lower'] ?? 0, 2) }}</td>
                                        <td>{{ number_format($b['upper'] ?? 0, 2) }}</td>
                                        <td>{{ $b['outliers'] ?? 0 }}</td>
                                        <td>{{ implode(', ', $outlierNames[$key] ?? []) ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(count($dataWinsorized) > 0)
                        <h6 class="mb-3" style="color: var(--text-primary);">Data Setelah Winsorization (count)</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-modern">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Desa</th>
                                        @foreach($fiturLabels as $label)
                                            <th>{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataWinsorized as $wi => $row)
                                        <tr>
                                            <td>{{ $wi + 1 }}</td>
                                            <td style="font-weight: 500;">{{ $row['desa_nama'] }}</td>
                                            @foreach($fiturKeys as $key)
                                                <td>{{ number_format($row[$key] ?? 0, 2) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <h6 class="mb-3" style="color: var(--text-primary);">
                        <i class="fas fa-table me-2"></i>Normalisasi Z-score Populasi
                    </h6>
                    <x-admin.alert variant="info" class="mb-3">
                        <code>z = (x − mean) / std_pop</code> dengan <code>std_pop = sqrt(sum((x−mean)²)/N)</code> (equiv. STDEV.P),
                        dihitung setelah Winsorization. Jarak Euclidean dihitung di ruang Z-score.
                    </x-admin.alert>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm" style="color: var(--text-primary);">
                            <thead>
                                <tr>
                                    <th>Fitur</th>
                                    <th>Mean</th>
                                    <th>Std Populasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fiturLabels as $key => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>{{ number_format($meanStd['mean'][$key] ?? 0, 4) }}</td>
                                        <td>{{ number_format($meanStd['std'][$key] ?? 0, 4) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-modern">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Desa</th>
                                    @foreach($fiturLabels as $label)
                                        <th>{{ $label }}</th>
                                    @endforeach
                                    <th>Elbow Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataNormalisasi as $ni => $row)
                                    <tr>
                                        <td>{{ $ni + 1 }}</td>
                                        <td style="font-weight: 500;">{{ $row['desa_nama'] }}</td>
                                        @foreach($fiturKeys as $key)
                                            <td>{{ number_format($row[$key] ?? 0, 4) }}</td>
                                        @endforeach
                                        <td>{{ number_format($elbowPerDesa[$ni] ?? 0, 4) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="{{ 2 + count($fiturKeys) }}" class="text-end fw-semibold">Hasil Elbow Method / WCSS (K=1)</td>
                                    <td class="fw-semibold">{{ number_format($wcss['k1'] ?? array_sum($elbowPerDesa), 4) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if(count($centroidsNormalized) > 0)
                        <h6 class="mb-3" style="color: var(--text-primary);">Centroid Ternormalisasi (Z-score)</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm" style="color: var(--text-primary);">
                                <thead>
                                    <tr>
                                        <th>Cluster</th>
                                        @foreach($fiturLabels as $label)
                                            <th>{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($centroidsNormalized as $i => $centroid)
                                        <tr>
                                            <td>
                                                <x-admin.badge :variant="\App\Services\KMeansService::getClusterColor($i)">
                                                    {{ \App\Services\KMeansService::getClusterLabel($i) }}
                                                </x-admin.badge>
                                            </td>
                                            @foreach($fiturKeys as $key)
                                                <td>{{ number_format($centroid[$key] ?? 0, 4) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if(!empty($wcss))
                        <h6 class="mb-3" style="color: var(--text-primary);">
                            <i class="fas fa-chart-line me-2"></i>Elbow / WCSS
                        </h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm" style="color: var(--text-primary);">
                                <thead>
                                    <tr>
                                        <th>K</th>
                                        <th>WCSS</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>K=1</td>
                                        <td>{{ number_format($wcss['k1'] ?? 0, 4) }}</td>
                                        <td>Total sum-of-squares (baseline)</td>
                                    </tr>
                                    <tr>
                                        <td>K={{ $selectedPeriode->jumlah_cluster }}</td>
                                        <td>{{ number_format($wcss['final'] ?? 0, 4) }}</td>
                                        <td>Jumlah kuadrat jarak minimum ke centroid akhir</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if(count($iterationsHistory) > 0)
                        <h6 class="mb-3" style="color: var(--text-primary);">
                            <i class="fas fa-redo me-2"></i>Penerapan K-Means
                        </h6>
                        @foreach($iterationsHistory as $hi => $h)
                            @php
                                $iterNo = $h['iteration'] ?? ($hi + 1);
                                $iterName = $iterNo == 1 ? 'ITERASI PERTAMA' : ($iterNo == 2 ? 'ITERASI KEDUA' : 'ITERASI '.$iterNo);
                                $hCentroids = array_values($h['centroids_normalized'] ?? []);
                            @endphp
                            <div class="mb-2"><strong style="color: var(--text-primary);">{{ $iterName }}</strong> <small class="text-muted">WCSS={{ number_format($h['wcss'] ?? 0, 4) }}</small></div>
                            @if(count($hCentroids) > 0)
                                <div class="table-responsive mb-2">
                                    <table class="table table-sm" style="color: var(--text-primary);">
                                        <thead>
                                            <tr>
                                                <th>Centroid</th>
                                                @foreach($fiturLabels as $label)
                                                    <th>{{ $label }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hCentroids as $cci => $cc)
                                                <tr>
                                                    <td class="fw-semibold">Centroid{{ $cci + 1 }}</td>
                                                    @foreach($fiturKeys as $key)
                                                        <td>{{ number_format($cc[$key] ?? 0, 4) }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-modern">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Desa</th>
                                            @for($ci = 0; $ci < $selectedPeriode->jumlah_cluster; $ci++)
                                                <th>C{{ $ci + 1 }}</th>
                                            @endfor
                                            <th>Cluster</th>
                                            <th>Jarak Minimum</th>
                                            <th>WCSS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(($h['assignments'] ?? []) as $idx => $a)
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td style="font-weight: 500;">{{ $dataNormalisasi[$idx]['desa_nama'] ?? ('#'.$idx) }}</td>
                                                @foreach(($a['distances'] ?? []) as $d)
                                                    <td>{{ number_format($d, 4) }}</td>
                                                @endforeach
                                                <td>{{ ($a['cluster_pos'] ?? 0) + 1 }}</td>
                                                <td>{{ number_format($a['min_distance'] ?? 0, 4) }}</td>
                                                <td>{{ number_format($a['wcss'] ?? 0, 4) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="{{ 3 + $selectedPeriode->jumlah_cluster }}" class="text-end fw-semibold">Elbow M (WCSS {{ $iterName }})</td>
                                            <td colspan="2" class="fw-semibold">{{ number_format($h['wcss'] ?? 0, 4) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endforeach

                        <h6 class="mb-3" style="color: var(--text-primary);">
                            <i class="fas fa-flag-checkered me-2"></i>Hasil Akhir
                        </h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-modern">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Cluster 1 (Risiko Tinggi)</th>
                                        <th>Cluster 2 (Risiko Rendah)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($ri = 0; $ri < $maxHasilRows; $ri++)
                                        <tr>
                                            <td>{{ $ri + 1 }}</td>
                                            <td style="font-weight: 500;">{{ $hasilByCluster[1][$ri] ?? '' }}</td>
                                            <td style="font-weight: 500;">{{ $hasilByCluster[2][$ri] ?? '' }}</td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    @endif
                @else
                    <h6 class="mb-3" style="color: var(--text-primary);">
                        <i class="fas fa-table me-2"></i>Normalisasi Data (Min-Max, 0–1) — Riwayat Lama
                    </h6>
                    <x-admin.alert variant="warning" class="mb-3">
                        Analisis lama memakai <code>(nilai − min) / (max − min)</code> pada persentase. Jalankan analisis baru untuk pipeline Winsorization IQR + Z-score populasi (count mentah).
                    </x-admin.alert>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm" style="color: var(--text-primary);">
                            <thead>
                                <tr>
                                    <th>Fitur</th>
                                    <th>Min (asli)</th>
                                    <th>Max (asli)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fiturLabels as $key => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>{{ number_format($minMax['min'][$key] ?? 0, 2) }}</td>
                                        <td>{{ number_format($minMax['max'][$key] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-modern">
                            <thead>
                                <tr>
                                    <th>Desa</th>
                                    @foreach($fiturLabels as $label)
                                        <th>{{ str_replace(' (%)', '', $label) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataNormalisasi as $row)
                                    <tr>
                                        <td style="font-weight: 500;">{{ $row['desa_nama'] }}</td>
                                        @foreach($fiturKeys as $key)
                                            <td>{{ number_format($row[$key] ?? 0, 4) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(count($centroidsNormalized) > 0)
                        <h6 class="mb-3" style="color: var(--text-primary);">Centroid Ternormalisasi (0–1)</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm" style="color: var(--text-primary);">
                                <thead>
                                    <tr>
                                        <th>Cluster</th>
                                        @foreach($fiturLabels as $label)
                                            <th>{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($centroidsNormalized as $i => $centroid)
                                        <tr>
                                            <td>
                                                <x-admin.badge :variant="\App\Services\KMeansService::getClusterColor($i)">
                                                    {{ \App\Services\KMeansService::getClusterLabel($i) }}
                                                </x-admin.badge>
                                            </td>
                                            @foreach($fiturKeys as $key)
                                                <td>{{ number_format($centroid[$key] ?? 0, 4) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif

                <h6 class="mb-3" style="color: var(--text-primary);">
                    <i class="fas fa-chart-scatter me-2"></i>Scatter Plot (Stunting vs Gizi Kurang — count)
                </h6>
                @php
                    $chartData = $selectedPeriode->hasilCluster->map(function($h) {
                        return [
                            'x' => (float) ($h->rekap->jumlah_stunting ?? 0),
                            'y' => (float) ($h->rekap->jumlah_gizi_kurang ?? 0),
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

                @php $centroidAwal = $selectedPeriode->getCentroidsInitial(); @endphp
                @if(count($centroidAwal) > 0)
                    <h6 class="mb-3" style="color: var(--text-primary);">
                        Centroid Awal yang Digunakan
                        @if($selectedPeriode->centroid_manual)
                            (skala Z-score)
                            <x-admin.badge variant="primary">Manual</x-admin.badge>
                        @else
                            (count — satuan asli)
                            <x-admin.badge variant="secondary">Otomatis</x-admin.badge>
                        @endif
                    </h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm" style="color: var(--text-primary);">
                            <thead>
                                <tr>
                                    <th>Cluster</th>
                                    @foreach($fiturLabels as $label)
                                        <th>{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($centroidAwal as $i => $centroid)
                                    <tr>
                                        <td>
                                            <x-admin.badge :variant="\App\Services\KMeansService::getClusterColor($i)">
                                                {{ \App\Services\KMeansService::getClusterLabel($i) }}
                                            </x-admin.badge>
                                        </td>
                                        @foreach(array_keys($fiturLabels) as $key)
                                            <td>{{ number_format($centroid[$key] ?? 0, $selectedPeriode->centroid_manual ? 4 : 2) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($selectedPeriode->data_centroid)
                    <h6 class="mb-3" style="color: var(--text-primary);">Nilai Centroid Akhir (count — satuan asli)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm" style="color: var(--text-primary);">
                            <thead>
                                <tr>
                                    <th>Cluster</th>
                                    @foreach($fiturLabels as $label)
                                        <th>{{ $label }}</th>
                                    @endforeach
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
                                        @foreach(array_keys($fiturLabels) as $key)
                                            <td>{{ number_format($centroid[$key] ?? 0, 1) }}</td>
                                        @endforeach
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
                                    <th>Stunting</th>
                                    <th>Gizi Kurang</th>
                                    <th>BB Kurang</th>
                                    <th>Gizi Lebih</th>
                                    <th>Gizi Baik</th>
                                    <th>Cluster</th>
                                    <th>Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($desaStats as $index => $stat)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td style="font-weight: 500;">{{ $stat['nama_desa'] }}</td>
                                        <td>{{ $stat['jumlah_stunting'] }}</td>
                                        <td>{{ $stat['jumlah_gizi_kurang'] }}</td>
                                        <td>{{ $stat['jumlah_bb_kurang'] }}</td>
                                        <td>{{ $stat['jumlah_gizi_lebih'] ?? '-' }}</td>
                                        <td>{{ $stat['jumlah_gizi_baik'] ?? '-' }}</td>
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
                        <i class="fas fa-chart-bar me-2"></i>Grafik Count Indikator per Desa
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
                1: { bg: 'rgba(220,53,69,0.6)', border: 'rgb(220,53,69)' },
                2: { bg: 'rgba(40,167,69,0.6)', border: 'rgb(40,167,69)' }
            };
            const labels = { 0: 'Risiko Rendah', 1: 'Risiko Tinggi', 2: 'Risiko Rendah' };
            const datasets = [];
            const maxK = Math.max(1, ...chartData.map(d => d.cluster));
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
                    data: centroidsData.map(c => ({ x: c.jumlah_stunting ?? c.persentase_stunting ?? 0, y: c.jumlah_gizi_kurang ?? c.persentase_gizi_kurang ?? 0 })),
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
                        title: { display: true, text: 'Pemetaan Desa (Stunting vs Gizi Kurang — count)' },
                        tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${c.raw.nama} (S: ${c.raw.x}, GK: ${c.raw.y})` } }
                    },
                    scales: {
                        x: { title: { display: true, text: 'Stunting (count)' } },
                        y: { title: { display: true, text: 'Gizi Kurang (count)' } }
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
                        { label: 'Stunting', data: stats.map(d => d.jumlah_stunting ?? 0), backgroundColor: 'rgba(220,53,69,0.8)' },
                        { label: 'Gizi Kurang', data: stats.map(d => d.jumlah_gizi_kurang ?? 0), backgroundColor: 'rgba(255,193,7,0.8)' },
                        { label: 'BB Kurang', data: stats.map(d => d.jumlah_bb_kurang ?? 0), backgroundColor: 'rgba(13,110,253,0.8)' },
                        { label: 'Gizi Lebih', data: stats.map(d => d.jumlah_gizi_lebih ?? 0), backgroundColor: 'rgba(23,162,184,0.8)' },
                        { label: 'Gizi Baik', data: stats.map(d => d.jumlah_gizi_baik ?? 0), backgroundColor: 'rgba(40,167,69,0.8)' }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { title: { display: true, text: 'Count Indikator per Desa' } },
                    scales: { x: { ticks: { maxRotation: 45, minRotation: 45 } }, y: { beginAtZero: true, title: { display: true, text: 'Jiwa' } } }
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
