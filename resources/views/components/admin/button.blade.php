@props([
    'type' => 'button',
    'variant' => 'primary', // 'primary', 'secondary', 'success', 'warning', 'danger', 'outline'
    'size' => 'md', // 'sm', 'md', 'lg'
    'icon' => null,
    'iconPosition' => 'left', // 'left' or 'right'
    'href' => null
])
@php
    $variantStyles = [
        'primary' => 'background: var(--primary-color); color: white; border: none;',
        'secondary' => 'background: var(--secondary-color); color: white; border: none;',
        'success' => 'background: var(--success-color); color: white; border: none;',
        'warning' => 'background: var(--warning-color); color: white; border: none;',
        'danger' => 'background: var(--danger-color); color: white; border: none;',
        'outline' => 'background: white; color: #64748b; border: 1px solid #e2e8f0;',
    ];

    $sizeClasses = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];

    $style = $variantStyles[$variant] ?? $variantStyles['primary'];
    $sizeClass = $sizeClasses[$size] ?? '';
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "btn btn-modern {$sizeClass}"]) }} style="{{ $style }}">
        @if($icon && $iconPosition === 'left')
            <i class="{{ $icon }} me-2"></i>
        @endif
        {{ $slot }}
    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} ms-2"></i>
    @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "btn btn-modern {$sizeClass}"]) }} style="{{ $style }}">
        @if($icon && $iconPosition === 'left')
            <i class="{{ $icon }} me-2"></i>
        @endif
            {{ $slot }}
            @if($icon && $iconPosition === 'right')
                <i class="{{ $icon }} ms-2"></i>
            @endif
        </button>
@endif
