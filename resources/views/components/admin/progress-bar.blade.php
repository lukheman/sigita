@props([
    'value' => 0,
    'label' => null,
    'showPercentage' => true,
    'variant' => 'primary' // 'primary', 'secondary', 'success', 'warning', 'danger'
])

@php
    $colors = [
        'primary' => 'var(--primary-color)',
        'secondary' => 'var(--secondary-color)',
        'success' => 'var(--success-color)',
        'warning' => 'var(--warning-color)',
        'danger' => 'var(--danger-color)',
    ];
    $color = $colors[$variant] ?? $colors['primary'];
    $percentage = min(100, max(0, $value));
@endphp

<div {{ $attributes }}>
    @if($label || $showPercentage)
        <div class="d-flex justify-content-between mb-2">
            @if($label)
                <small class="text-muted">{{ $label }}</small>
            @endif
            @if($showPercentage)
                <small class="fw-semibold" style="color: {{ $color }};">{{ $percentage }}%</small>
            @endif
        </div>
    @endif
    <div class="progress-modern">
        <div class="progress-bar-modern" style="width: {{ $percentage }}%; background: {{ $color }};"></div>
    </div>
</div>
