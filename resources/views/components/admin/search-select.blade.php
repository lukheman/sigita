@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Cari atau pilih...',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'searchable' => true,
])

@php
    $inputId = $id ?? $name ?? 'search-select-' . uniqid();
@endphp

<div class="form-group" x-data="{
    open: false,
    search: '',
    selected: '{{ $selected }}',
    selectedLabel: '{{ $options[$selected] ?? '' }}',
    options: {{ json_encode($options) }},
    get filteredOptions() {
        if (!this.search) return Object.entries(this.options);
        const searchLower = this.search.toLowerCase();
        return Object.entries(this.options).filter(([key, label]) => 
            label.toLowerCase().includes(searchLower)
        );
    },
    selectOption(value, label) {
        this.selected = value;
        this.selectedLabel = label;
        this.open = false;
        this.search = '';
    },
    clearSelection() {
        this.selected = '';
        this.selectedLabel = '';
    }
}" @click.away="open = false">
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span style="color: var(--danger-color);">*</span>
            @endif
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" :value="selected" {{ $required ? 'required' : '' }}>

    <div class="search-select-wrapper position-relative">
        <div 
            class="search-select-trigger d-flex align-items-center justify-content-between {{ $error ? 'is-invalid' : '' }}"
            @click="open = !open"
            :class="{ 'active': open }"
        >
            <span x-text="selectedLabel || '{{ $placeholder }}'" :style="selectedLabel ? 'color: var(--text-primary)' : 'color: var(--text-muted)'"></span>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-link p-0" x-show="selected" @click.stop="clearSelection()" style="color: var(--text-muted);">
                    <i class="fas fa-times"></i>
                </button>
                <i class="fas fa-chevron-down" :class="{ 'rotate-180': open }" style="transition: transform 0.2s;"></i>
            </div>
        </div>

        <div class="search-select-dropdown" x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95">
            
            @if($searchable)
                <div class="search-select-search p-2">
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Cari..." 
                        class="form-control"
                        @click.stop
                    >
                </div>
            @endif

            <div class="search-select-options">
                <template x-if="filteredOptions.length === 0">
                    <div class="search-select-empty p-3 text-center">
                        <span style="color: var(--text-muted);">Tidak ditemukan</span>
                    </div>
                </template>
                <template x-for="[value, label] in filteredOptions" :key="value">
                    <div 
                        class="search-select-option"
                        :class="{ 'selected': selected === value }"
                        @click="selectOption(value, label)"
                        x-text="label"
                    ></div>
                </template>
            </div>
        </div>
    </div>

    @if($error)
        <div class="invalid-feedback d-block">{{ $error }}</div>
    @endif

    @if($hint && !$error)
        <div class="form-text" style="color: var(--text-muted);">{{ $hint }}</div>
    @endif
</div>

<style>
    .search-select-trigger {
        background: var(--input-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .search-select-trigger:hover,
    .search-select-trigger.active {
        border-color: var(--primary-color);
    }

    .search-select-trigger.is-invalid {
        border-color: var(--danger-color);
    }

    .search-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1000;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-top: 4px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        max-height: 300px;
        overflow: hidden;
    }

    .search-select-search .form-control {
        background: var(--input-bg);
        border-color: var(--border-color);
        color: var(--text-primary);
    }

    .search-select-options {
        max-height: 240px;
        overflow-y: auto;
        padding: 0.5rem;
    }

    .search-select-option {
        padding: 0.625rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        color: var(--text-primary);
        transition: all 0.2s;
    }

    .search-select-option:hover {
        background: var(--hover-bg);
    }

    .search-select-option.selected {
        background: var(--primary-color);
        color: white;
    }

    .rotate-180 {
        transform: rotate(180deg);
    }
</style>
