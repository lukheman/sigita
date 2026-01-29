@props([
    'variant' => 'primary', // 'primary', 'secondary', 'success', 'warning', 'danger', 'info'
    'icon' => null
])
@php
    $variants = [
        'primary' => ['rgba(99, 102, 241, 0.1)', 'var(--primary-color)'],
        'secondary' => ['rgba(14, 165, 233, 0.1)', 'var(--secondary-color)'],
        'success' => ['rgba(16, 185, 129, 0.1)', 'var(--success-color)'],
        'warning' => ['rgba(245, 158, 11, 0.1)', 'var(--warning-color)'],
        'danger' => ['rgba(239, 68, 68, 0.1)', 'var(--danger-color)'],
        'info' => ['rgba(100, 116, 139, 0.1)', '#64748b'],
    ];
    $bg = $variants[$variant][0] ?? $variants['primary'][0];
    $color = $variants[$variant][1] ?? $variants['primary'][1];

    // Default icons for variants
    $defaultIcons = [
        'primary' => 'fas fa-circle',
        'success' => 'fas fa-check-circle',
        'warning' => 'fas fa-exclamation-circle',
        'danger' => 'fas fa-times-circle',
        'info' => 'fas fa-info-circle',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'badge-modern']) }} style="background: {{ $bg }}; color: {{ $color }};">
    @if($icon)
        <i class="{{ $icon }}" @if($icon === 'fas fa-circle') style="font-size: 0.5rem;" @endif></i>
    @endif
    {{ $slot }}
</span>
