@props([
    'icon' => 'fas fa-star',
    'title',
    'description' => null,
    'variant' => 'primary' // 'primary', 'secondary', 'success', 'warning', 'danger'
])
@php
    $colors = [
        'primary' => ['var(--primary-color)', 'rgba(99, 102, 241, 0.1)'],
        'secondary' => ['var(--secondary-color)', 'rgba(14, 165, 233, 0.1)'],
        'success' => ['var(--success-color)', 'rgba(16, 185, 129, 0.1)'],
        'warning' => ['var(--warning-color)', 'rgba(245, 158, 11, 0.1)'],
        'danger' => ['var(--danger-color)', 'rgba(239, 68, 68, 0.1)'],
    ];
    $color = $colors[$variant][0] ?? $colors['primary'][0];
    $bgColor = $colors[$variant][1] ?? $colors['primary'][1];
@endphp

<div class="modern-card text-center">
<div class="stat-icon mx-auto" style="background: {{ $bgColor }}; color: {{ $color }};">
        <i class="{{ $icon }}"></i>
    </div>
    <h5 style="color: var(--text-primary);">{{ $title }}</h5>
    @if($description)
        <p class="text-muted mb-0" style="font-size: 0.875rem;">{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
