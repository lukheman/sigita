<div>
<div>
    {{-- Page Header --}}
    <div class="docs-header mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="docs-title">
                    <i class="fas fa-cubes me-2" style="color: var(--primary-color);"></i>
                    Component Library
                </h1>
                <p class="docs-subtitle mb-0">Comprehensive UI component documentation with live examples</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.badge variant="primary" icon="fas fa-cubes">33 Components</x-admin.badge>
                <x-admin.badge variant="success" icon="fas fa-moon">Dark Mode Ready</x-admin.badge>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Sidebar Navigation --}}
        <div class="col-lg-3">
            <div class="docs-sidebar position-sticky" style="top: 90px;">
                {{-- Search --}}
                <div class="docs-search mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search components..."
                            wire:model.live.debounce.300ms="searchQuery">
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="docs-nav">
                    @foreach($sections as $key => $section)
                        <button wire:click="setSection('{{ $key }}')"
                            class="docs-nav-item {{ $activeSection === $key ? 'active' : '' }}">
                            <div class="d-flex align-items-center gap-2">
                                <i class="{{ $section['icon'] }}"></i>
                                <span>{{ $section['label'] }}</span>
                            </div>
                            <span class="docs-nav-count">{{ $section['count'] }}</span>
                        </button>
                    @endforeach
                </nav>

                {{-- Quick Stats --}}
                <div class="docs-stats mt-4">
                    <div class="docs-stat">
                        <div class="docs-stat-value">33</div>
                        <div class="docs-stat-label">Components</div>
                    </div>
                    <div class="docs-stat">
                        <div class="docs-stat-value">11</div>
                        <div class="docs-stat-label">Categories</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="col-lg-9">
            {{-- Buttons Section --}}
            @if($activeSection === 'buttons')
                @include('livewire.admin.partials.docs-buttons')
            @endif

            {{-- Badges Section --}}
            @if($activeSection === 'badges')
                @include('livewire.admin.partials.docs-badges')
            @endif

            {{-- Alerts Section --}}
            @if($activeSection === 'alerts')
                @include('livewire.admin.partials.docs-alerts')
            @endif

            {{-- Forms Section --}}
            @if($activeSection === 'forms')
                @include('livewire.admin.partials.docs-forms')
            @endif

            {{-- Selects Section --}}
            @if($activeSection === 'selects')
                @include('livewire.admin.partials.docs-selects')
            @endif

            {{-- Toggles Section --}}
            @if($activeSection === 'toggles')
                @include('livewire.admin.partials.docs-toggles')
            @endif

            {{-- Cards Section --}}
            @if($activeSection === 'cards')
                @include('livewire.admin.partials.docs-cards')
            @endif

            {{-- Tables Section --}}
            @if($activeSection === 'tables')
                @include('livewire.admin.partials.docs-tables')
            @endif

            {{-- Navigation Section --}}
            @if($activeSection === 'navigation')
                @include('livewire.admin.partials.docs-navigation')
            @endif

            {{-- Feedback Section --}}
            @if($activeSection === 'feedback')
                @include('livewire.admin.partials.docs-feedback')
            @endif

            {{-- Media Section --}}
            @if($activeSection === 'media')
                @include('livewire.admin.partials.docs-media')
            @endif
        </div>
    </div>
</div>

<style>
    .docs-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
        border-radius: 20px;
        color: white;
    }

    .docs-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.25rem;
    }

    .docs-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.95rem;
    }

    .docs-sidebar {
        background: var(--bg-secondary);
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: var(--card-shadow);
    }

    .docs-search .input-group-text {
        background: var(--input-bg);
        border-color: var(--border-color);
        color: var(--text-muted);
    }

    .docs-search .form-control {
        background: var(--input-bg);
        border-color: var(--border-color);
        color: var(--text-primary);
    }

    .docs-nav {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .docs-nav-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: transparent;
        border: none;
        text-align: left;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        color: var(--text-secondary);
        font-weight: 500;
        transition: all 0.2s;
        cursor: pointer;
        width: 100%;
    }

    .docs-nav-item:hover {
        background: var(--hover-bg);
        color: var(--primary-color);
    }

    .docs-nav-item.active {
        background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
        color: white;
    }

    .docs-nav-item.active .docs-nav-count {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .docs-nav-item i {
        width: 20px;
        font-size: 0.9rem;
    }

    .docs-nav-count {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
        background: var(--bg-tertiary);
        border-radius: 20px;
        color: var(--text-muted);
    }

    .docs-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        padding: 1rem;
        background: var(--bg-tertiary);
        border-radius: 12px;
    }

    .docs-stat {
        text-align: center;
    }

    .docs-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .docs-stat-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Component Card */
    .docs-card {
        background: var(--bg-secondary);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-color);
    }

    .docs-card-header {
        padding: 1rem 1.25rem;
        background: var(--bg-tertiary);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .docs-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .docs-card-title i {
        color: var(--primary-color);
    }

    .docs-card-actions {
        display: flex;
        gap: 0.5rem;
    }

    .docs-copy-btn {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .docs-copy-btn:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .docs-code {
        background: #0f172a;
        padding: 1.25rem;
        overflow-x: auto;
        position: relative;
    }

    .docs-code pre {
        margin: 0;
    }

    .docs-code code {
        font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
        font-size: 0.8125rem;
        line-height: 1.6;
        color: #e2e8f0;
        white-space: pre;
    }

    /* Syntax Highlighting */
    .docs-code .tag {
        color: #f472b6;
    }

    .docs-code .attr {
        color: #7dd3fc;
    }

    .docs-code .string {
        color: #86efac;
    }

    .docs-code .comment {
        color: #64748b;
    }

    .docs-preview {
        padding: 2rem;
        background: var(--bg-secondary);
    }

    .docs-preview-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-muted);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .docs-preview-label::before {
        content: '';
        width: 8px;
        height: 8px;
        background: var(--success-color);
        border-radius: 50%;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    /* Section Header */
    .docs-section-header {
        margin-bottom: 1.5rem;
    }

    .docs-section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .docs-section-title i {
        color: var(--primary-color);
        font-size: 1.25rem;
    }

    .docs-section-desc {
        color: var(--text-muted);
        margin-bottom: 0;
    }

    /* Props Table */
    .docs-props-table {
        width: 100%;
        border-collapse: collapse;
    }

    .docs-props-table th,
    .docs-props-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .docs-props-table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        font-weight: 600;
    }

    .docs-props-table td {
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .docs-props-table code {
        background: var(--bg-tertiary);
        padding: 0.125rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        color: var(--primary-color);
    }

    .docs-prop-required {
        color: var(--danger-color);
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 0.25rem;
    }
</style>

<script>
    function copyCode(button) {
        const codeBlock = button.closest('.docs-card').querySelector('code');
        const text = codeBlock.textContent;

        navigator.clipboard.writeText(text).then(() => {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Copied!';
            button.style.background = 'var(--success-color)';
            button.style.color = 'white';

            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = '';
                button.style.color = '';
            }, 2000);
        });
    }
</script>
</div>
