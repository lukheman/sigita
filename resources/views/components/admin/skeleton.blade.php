@props([
    'type' => 'text', // text, circle, rect, card, table
    'width' => '100%',
    'height' => '20px',
    'count' => 1,
    'animated' => true,
])

@php
    $types = [
        'text' => ['height' => '16px', 'radius' => '4px'],
        'circle' => ['height' => '48px', 'radius' => '50%'],
        'rect' => ['height' => '100px', 'radius' => '8px'],
        'card' => ['height' => '200px', 'radius' => '16px'],
    ];
    $config = $types[$type] ?? $types['text'];
    $finalHeight = $height !== '20px' ? $height : $config['height'];
@endphp

@if($type === 'table')
    <div class="skeleton-table">
        @for($i = 0; $i < $count; $i++)
            <div class="skeleton-row d-flex gap-3 mb-3">
                <div class="skeleton {{ $animated ? 'skeleton-animated' : '' }}" style="width: 40px; height: 40px; border-radius: 50%;"></div>
                <div class="flex-grow-1">
                    <div class="skeleton {{ $animated ? 'skeleton-animated' : '' }}" style="width: 60%; height: 16px; border-radius: 4px; margin-bottom: 8px;"></div>
                    <div class="skeleton {{ $animated ? 'skeleton-animated' : '' }}" style="width: 40%; height: 12px; border-radius: 4px;"></div>
                </div>
                <div class="skeleton {{ $animated ? 'skeleton-animated' : '' }}" style="width: 80px; height: 32px; border-radius: 8px;"></div>
            </div>
        @endfor
    </div>
@elseif($type === 'card')
    <div class="skeleton-card modern-card p-0 overflow-hidden">
        <div class="skeleton {{ $animated ? 'skeleton-animated' : '' }}" style="width: 100%; height: 150px;"></div>
        <div class="p-4">
            <div class="skeleton {{ $animated ? 'skeleton-animated' : '' }}" style="width: 70%; height: 20px; border-radius: 4px; margin-bottom: 12px;"></div>
            <div class="skeleton {{ $animated ? 'skeleton-animated' : '' }}" style="width: 100%; height: 14px; border-radius: 4px; margin-bottom: 8px;"></div>
            <div class="skeleton {{ $animated ? 'skeleton-animated' : '' }}" style="width: 80%; height: 14px; border-radius: 4px;"></div>
        </div>
    </div>
@else
    @for($i = 0; $i < $count; $i++)
        <div 
            class="skeleton {{ $animated ? 'skeleton-animated' : '' }}" 
            style="width: {{ $width }}; height: {{ $finalHeight }}; border-radius: {{ $config['radius'] }}; {{ $count > 1 ? 'margin-bottom: 8px;' : '' }}"
            {{ $attributes }}
        ></div>
    @endfor
@endif

<style>
    .skeleton {
        background: var(--border-color);
        position: relative;
        overflow: hidden;
    }

    .skeleton-animated::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.1),
            transparent
        );
        animation: skeleton-shimmer 1.5s infinite;
    }

    [data-theme="dark"] .skeleton-animated::after {
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.05),
            transparent
        );
    }

    @keyframes skeleton-shimmer {
        0% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(100%);
        }
    }
</style>
