@props([
    'icon' => 'fas fa-inbox',
    'title' => 'Tidak ada data',
    'description' => 'Belum ada data yang tersedia saat ini.',
    'actionLabel' => null,
    'actionHref' => null,
    'actionIcon' => 'fas fa-plus',
    'size' => 'md', // sm, md, lg
])

@php
    $sizes = [
        'sm' => ['icon' => '2.5rem', 'title' => '1rem', 'desc' => '0.8125rem', 'padding' => '2rem'],
        'md' => ['icon' => '4rem', 'title' => '1.25rem', 'desc' => '0.9375rem', 'padding' => '3rem'],
        'lg' => ['icon' => '5rem', 'title' => '1.5rem', 'desc' => '1rem', 'padding' => '4rem'],
    ];
    $sizeConfig = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="empty-state text-center" style="padding: {{ $sizeConfig['padding'] }};" {{ $attributes }}>
    <div class="empty-state-icon mb-3">
        <i class="{{ $icon }}" style="font-size: {{ $sizeConfig['icon'] }}; color: var(--text-muted); opacity: 0.5;"></i>
    </div>
    
    <h5 class="empty-state-title mb-2" style="font-size: {{ $sizeConfig['title'] }}; color: var(--text-primary); font-weight: 600;">
        {{ $title }}
    </h5>
    
    <p class="empty-state-description mb-0" style="font-size: {{ $sizeConfig['desc'] }}; color: var(--text-muted); max-width: 400px; margin: 0 auto;">
        {{ $description }}
    </p>

    {{ $slot }}

    @if($actionLabel)
        <div class="mt-4">
            <a href="{{ $actionHref ?? '#' }}" class="btn btn-modern btn-primary-modern">
                <i class="{{ $actionIcon }} me-2"></i>{{ $actionLabel }}
            </a>
        </div>
    @endif
</div>

<style>
    .empty-state {
        background: var(--bg-secondary);
        border-radius: 16px;
        border: 2px dashed var(--border-color);
    }

    .empty-state-icon {
        animation: empty-bounce 2s ease-in-out infinite;
    }

    @keyframes empty-bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-10px);
        }
    }
</style>
