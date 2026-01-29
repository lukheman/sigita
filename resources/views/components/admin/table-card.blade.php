@props([
    'title' => null,
    'headers' => [],
    'viewAllHref' => null,
    'viewAllText' => 'View All'
])

<div class="modern-card">
    @if($title || $viewAllHref)
        <div class="d-flex justify-content-between align-items-center mb-3">
            @if($title)
                <h5 class="mb-0" style="color: #1e293b; font-weight: 600;">{{ $title }}</h5>
            @endif
            @if($viewAllHref)
                <a href="{{ $viewAllHref }}" class="btn btn-sm" style="color: var(--primary-color); background: rgba(99, 102, 241, 0.1); border: none; border-radius: 8px; padding: 0.5rem 1rem; font-weight: 500;">
                    {{ $viewAllText }}
                </a>
            @elseif(isset($actions))
                {{ $actions }}
            @endif
        </div>
    @endif

    <x-admin.table :headers="$headers">
        @if(isset($head))
            <x-slot:head>{{ $head }}</x-slot:head>
        @endif
        {{ $slot }}
        @if(isset($foot))
            <x-slot:foot>{{ $foot }}</x-slot:foot>
        @endif
    </x-admin.table>
</div>
