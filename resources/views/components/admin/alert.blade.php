@props([
    'variant' => 'info', // 'success', 'danger', 'warning', 'info'
    'title' => null,
    'icon' => null,
    'dismissible' => false
])
@php
    $variants = [
        'success' => ['rgba(16, 185, 129, 0.1)', 'var(--success-color)', 'fas fa-check-circle'],
        'danger' => ['rgba(239, 68, 68, 0.1)', 'var(--danger-color)', 'fas fa-exclamation-triangle'],
        'warning' => ['rgba(245, 158, 11, 0.1)', 'var(--warning-color)', 'fas fa-exclamation-circle'],
        'info' => ['rgba(14, 165, 233, 0.1)', 'var(--secondary-color)', 'fas fa-info-circle'],
    ];
    $bg = $variants[$variant][0] ?? $variants['info'][0];
    $color = $variants[$variant][1] ?? $variants['info'][1];
    $defaultIcon = $variants[$variant][2] ?? $variants['info'][2];
    $alertIcon = $icon ?? $defaultIcon;
@endphp
<div {{ $attributes->merge(['class' => 'alert-modern']) }} style="background: {{ $bg }}; color: {{ $color }};">
    <i class="{{ $alertIcon }}" style="font-size: 1.25rem;"></i>
    <div class="flex-grow-1">
        @if($title)
            <strong>{{ $title }}</strong>
        @endif
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="btn-close" style="font-size: 0.75rem;" aria-label="Close"></button>
    @endif
</div>
