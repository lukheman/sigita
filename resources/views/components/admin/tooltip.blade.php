@props([
    'content' => '',
    'position' => 'top', // top, bottom, left, right
])

<span class="tooltip-wrapper" data-bs-toggle="tooltip" data-bs-placement="{{ $position }}" title="{{ $content }}" {{ $attributes }}>
    {{ $slot }}
</span>

<style>
    .tooltip {
        --bs-tooltip-bg: var(--bg-tertiary);
        --bs-tooltip-color: var(--text-primary);
    }

    .tooltip .tooltip-inner {
        background: var(--bg-tertiary);
        color: var(--text-primary);
        font-size: 0.8125rem;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .tooltip .tooltip-arrow::before {
        border-top-color: var(--bg-tertiary);
    }

    .bs-tooltip-bottom .tooltip-arrow::before {
        border-bottom-color: var(--bg-tertiary);
    }

    .bs-tooltip-start .tooltip-arrow::before {
        border-left-color: var(--bg-tertiary);
    }

    .bs-tooltip-end .tooltip-arrow::before {
        border-right-color: var(--bg-tertiary);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
