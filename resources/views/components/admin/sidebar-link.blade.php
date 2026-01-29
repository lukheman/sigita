@props([
    'href' => '#',
    'icon' => 'fas fa-circle',
    'active' => false
])

<a href="{{ $href }}" {{ $attributes->merge(['class' => $active ? 'active' : '']) }}>
    <i class="{{ $icon }}"></i>
    <span>{{ $slot }}</span>
</a>
