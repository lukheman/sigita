@props([
    'title' => null,
    'padding' => true
])

<div {{ $attributes->merge(['class' => 'modern-card' . ($padding ? '' : ' p-0')]) }}>
    @if($title)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0" style="color: var(--text-primary); font-weight: 600;">{{ $title }}</h5>
            @if(isset($actions))
                {{ $actions }}
            @endif
        </div>
    @endif
    {{ $slot }}
</div>
