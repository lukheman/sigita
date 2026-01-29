@props([
    'src' => null,
    'alt' => 'Avatar',
    'name' => null,
    'size' => 'md', // xs, sm, md, lg, xl
    'rounded' => 'full', // full, lg, md, sm, none
    'status' => null, // online, offline, away, busy
    'statusPosition' => 'bottom-right', // bottom-right, bottom-left, top-right, top-left
])

@php
    $sizes = [
        'xs' => ['size' => '24px', 'font' => '0.625rem', 'status' => '8px'],
        'sm' => ['size' => '32px', 'font' => '0.75rem', 'status' => '10px'],
        'md' => ['size' => '40px', 'font' => '0.875rem', 'status' => '12px'],
        'lg' => ['size' => '56px', 'font' => '1.25rem', 'status' => '14px'],
        'xl' => ['size' => '80px', 'font' => '1.75rem', 'status' => '16px'],
    ];
    $sizeConfig = $sizes[$size] ?? $sizes['md'];

    $roundedClasses = [
        'full' => '50%',
        'lg' => '16px',
        'md' => '12px',
        'sm' => '8px',
        'none' => '0',
    ];
    $borderRadius = $roundedClasses[$rounded] ?? '50%';

    $statusColors = [
        'online' => '#10b981',
        'offline' => '#94a3b8',
        'away' => '#f59e0b',
        'busy' => '#ef4444',
    ];

    $statusPositions = [
        'bottom-right' => 'bottom: 0; right: 0;',
        'bottom-left' => 'bottom: 0; left: 0;',
        'top-right' => 'top: 0; right: 0;',
        'top-left' => 'top: 0; left: 0;',
    ];

    // Generate initials from name
    $initials = '';
    if ($name && !$src) {
        $words = explode(' ', $name);
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        $initials = substr($initials, 0, 2);
    }
@endphp

<div class="avatar-wrapper position-relative d-inline-block" {{ $attributes }}>
    @if($src)
        <img 
            src="{{ $src }}" 
            alt="{{ $alt }}" 
            style="width: {{ $sizeConfig['size'] }}; height: {{ $sizeConfig['size'] }}; border-radius: {{ $borderRadius }}; object-fit: cover;"
        >
    @else
        <div 
            class="avatar-placeholder d-flex align-items-center justify-content-center"
            style="width: {{ $sizeConfig['size'] }}; height: {{ $sizeConfig['size'] }}; border-radius: {{ $borderRadius }}; font-size: {{ $sizeConfig['font'] }}; background: var(--primary-color); color: white; font-weight: 600;"
        >
            {{ $initials ?: $slot }}
        </div>
    @endif

    @if($status)
        <span 
            class="avatar-status position-absolute"
            style="width: {{ $sizeConfig['status'] }}; height: {{ $sizeConfig['status'] }}; background: {{ $statusColors[$status] }}; border-radius: 50%; border: 2px solid var(--bg-secondary); {{ $statusPositions[$statusPosition] }}"
            title="{{ ucfirst($status) }}"
        ></span>
    @endif
</div>
