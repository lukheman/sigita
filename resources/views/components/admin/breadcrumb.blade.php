@props([
    'items' => [],
    'separator' => '/',
])

<nav aria-label="breadcrumb" {{ $attributes }}>
    <ol class="breadcrumb custom-breadcrumb mb-0">
        @foreach($items as $index => $item)
            @php
                $isLast = $index === count($items) - 1;
                $label = is_array($item) ? ($item['label'] ?? '') : $item;
                $href = is_array($item) ? ($item['href'] ?? null) : null;
                $icon = is_array($item) ? ($item['icon'] ?? null) : null;
            @endphp
            <li class="breadcrumb-item {{ $isLast ? 'active' : '' }}" {{ $isLast ? 'aria-current=page' : '' }}>
                @if($icon)
                    <i class="{{ $icon }} me-1"></i>
                @endif
                @if(!$isLast && $href)
                    <a href="{{ $href }}">{{ $label }}</a>
                @else
                    {{ $label }}
                @endif
            </li>
        @endforeach
        {{ $slot }}
    </ol>
</nav>

<style>
    .custom-breadcrumb {
        background: transparent;
        padding: 0;
        font-size: 0.875rem;
    }

    .custom-breadcrumb .breadcrumb-item {
        color: var(--text-muted);
    }

    .custom-breadcrumb .breadcrumb-item a {
        color: var(--text-secondary);
        text-decoration: none;
        transition: color 0.2s;
    }

    .custom-breadcrumb .breadcrumb-item a:hover {
        color: var(--primary-color);
    }

    .custom-breadcrumb .breadcrumb-item.active {
        color: var(--text-primary);
        font-weight: 500;
    }

    .custom-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
        color: var(--text-muted);
        content: "{{ $separator }}";
    }

    .custom-breadcrumb .breadcrumb-item i {
        font-size: 0.8rem;
    }
</style>
