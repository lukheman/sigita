@props([
    'id' => null,
    'align' => 'start', // start, end, center
    'width' => 'auto', // auto, full, or specific width like '200px'
])

@php
    $alignmentClasses = [
        'start' => 'dropdown-menu-start',
        'end' => 'dropdown-menu-end',
        'center' => '',
    ];
@endphp

<div class="dropdown" {{ $attributes }}>
    <div data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
        {{ $trigger }}
    </div>
    <ul class="dropdown-menu {{ $alignmentClasses[$align] ?? '' }}" 
        style="background: var(--bg-secondary); border-color: var(--border-color); min-width: {{ $width }};">
        {{ $slot }}
    </ul>
</div>

<style>
    .dropdown-menu {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        padding: 0.5rem;
    }

    .dropdown-item {
        color: var(--text-primary);
        border-radius: 8px;
        padding: 0.625rem 1rem;
        transition: all 0.2s;
    }

    .dropdown-item:hover {
        background: var(--hover-bg);
        color: var(--primary-color);
    }

    .dropdown-item:active {
        background: var(--primary-color);
        color: white;
    }

    .dropdown-item i {
        width: 20px;
        margin-right: 8px;
    }

    .dropdown-divider {
        border-color: var(--border-color);
        margin: 0.5rem 0;
    }

    .dropdown-header {
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.5rem 1rem;
    }
</style>
