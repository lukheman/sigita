@props([
    'active' => null,
    'variant' => 'default', // default, pills, underline
])

@php
    $variantClasses = [
        'default' => 'nav-tabs',
        'pills' => 'nav-pills',
        'underline' => 'nav-tabs nav-tabs-underline',
    ];
@endphp

<div class="tabs-wrapper">
    <ul class="nav {{ $variantClasses[$variant] ?? 'nav-tabs' }}" role="tablist" {{ $attributes }}>
        {{ $slot }}
    </ul>
    @if(isset($content))
        <div class="tab-content">
            {{ $content }}
        </div>
    @endif
</div>

<style>
    .tabs-wrapper .nav-tabs {
        border-bottom: 2px solid var(--border-color);
        gap: 0.5rem;
    }

    .tabs-wrapper .nav-tabs .nav-link {
        color: var(--text-secondary);
        border: none;
        border-bottom: 2px solid transparent;
        background: transparent;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
        margin-bottom: -2px;
        border-radius: 8px 8px 0 0;
        transition: all 0.2s;
    }

    .tabs-wrapper .nav-tabs .nav-link:hover {
        color: var(--primary-color);
        border-bottom-color: var(--primary-light);
    }

    .tabs-wrapper .nav-tabs .nav-link.active {
        color: var(--primary-color);
        background: transparent;
        border-bottom: 2px solid var(--primary-color);
    }

    .tabs-wrapper .nav-pills {
        gap: 0.5rem;
        border: none;
    }

    .tabs-wrapper .nav-pills .nav-link {
        color: var(--text-secondary);
        background: var(--bg-tertiary);
        border-radius: 8px;
        padding: 0.625rem 1.25rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .tabs-wrapper .nav-pills .nav-link:hover {
        color: var(--primary-color);
        background: var(--hover-bg);
    }

    .tabs-wrapper .nav-pills .nav-link.active {
        color: white;
        background: var(--primary-color);
    }

    .tabs-wrapper .nav-tabs-underline {
        border-bottom: 1px solid var(--border-color);
    }

    .tabs-wrapper .nav-tabs-underline .nav-link {
        border-radius: 0;
    }

    .tabs-wrapper .tab-content {
        padding: 1.5rem 0;
    }

    .tabs-wrapper .tab-pane {
        color: var(--text-primary);
    }
</style>
