@props([
    'show' => false,
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed? This action cannot be undone.',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'variant' => 'danger',
    'icon' => 'fas fa-exclamation-triangle',
    'onConfirm' => null,
    'onCancel' => null
])

@php
    $iconColors = [
        'danger' => 'var(--danger-color)',
        'warning' => 'var(--warning-color)',
        'primary' => 'var(--primary-color)',
    ];
    $iconColor = $iconColors[$variant] ?? $iconColors['danger'];
@endphp

<div>
    @if ($show)


<div class="modal-backdrop-custom"
    @isset($onCancel)
        wire:click.self="{{ $onCancel }}"
    @endisset
>
    <div class="modal-content-custom" wire:click.stop>
        <div class="modal-header-custom">
            <h5 class="modal-title-custom">
                <i class="{{ $icon }} me-2" style="color: {{ $iconColor }};"></i>
                {{ $title }}
            </h5>
            <button type="button" class="modal-close-btn"
                @isset($onCancel)
                    wire:click="{{ $onCancel }}"
                @endisset
            >
                <i class="fas fa-times"></i>
            </button>
        </div>

        <p style="color: var(--text-secondary);">
            {{ $message }}
        </p>

        {{ $slot }}

        <div class="d-flex justify-content-end gap-2 mt-4">
            <x-admin.button
                type="button"
                variant="outline"
                wire:click="{{ $onCancel }}"
            >
                {{ $cancelText }}
            </x-admin.button>

            <x-admin.button
                type="button"
                :variant="$variant"
                    wire:click="{{ $onConfirm }}"
            >
                {{ $confirmText }}
            </x-admin.button>
        </div>
    </div>
</div>
@endif
    </div>
