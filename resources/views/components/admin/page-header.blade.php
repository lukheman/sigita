@props([
    'title',
    'subtitle' => null
])

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1" style="color: var(--text-primary);">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div>
            {{ $actions }}
        </div>
    @endif
</div>
