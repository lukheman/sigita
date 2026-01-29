<div class="docs-section-header">
    <h2 class="docs-section-title">
        <i class="fas fa-hand-pointer"></i>
        Buttons
    </h2>
    <p class="docs-section-desc">Interactive button components with multiple variants, sizes, and states.</p>
</div>

{{-- Button Variants --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-palette"></i>
            Button Variants
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.button variant="primary"&gt;Primary&lt;/x-admin.button&gt;
&lt;x-admin.button variant="secondary"&gt;Secondary&lt;/x-admin.button&gt;
&lt;x-admin.button variant="success"&gt;Success&lt;/x-admin.button&gt;
&lt;x-admin.button variant="warning"&gt;Warning&lt;/x-admin.button&gt;
&lt;x-admin.button variant="danger"&gt;Danger&lt;/x-admin.button&gt;
&lt;x-admin.button variant="outline"&gt;Outline&lt;/x-admin.button&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="d-flex flex-wrap gap-2">
            <x-admin.button variant="primary">Primary</x-admin.button>
            <x-admin.button variant="secondary">Secondary</x-admin.button>
            <x-admin.button variant="success">Success</x-admin.button>
            <x-admin.button variant="warning">Warning</x-admin.button>
            <x-admin.button variant="danger">Danger</x-admin.button>
            <x-admin.button variant="outline">Outline</x-admin.button>
        </div>
    </div>
</div>

{{-- Button with Icons & Sizes --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-expand-alt"></i>
            Icons & Sizes
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.button variant="primary" icon="fas fa-plus"&gt;With Icon&lt;/x-admin.button&gt;
&lt;x-admin.button variant="primary" size="sm"&gt;Small&lt;/x-admin.button&gt;
&lt;x-admin.button variant="primary"&gt;Default&lt;/x-admin.button&gt;
&lt;x-admin.button variant="primary" size="lg"&gt;Large&lt;/x-admin.button&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <x-admin.button variant="primary" icon="fas fa-plus">With Icon</x-admin.button>
            <x-admin.button variant="primary" size="sm">Small</x-admin.button>
            <x-admin.button variant="primary">Default</x-admin.button>
            <x-admin.button variant="primary" size="lg">Large</x-admin.button>
        </div>
    </div>
</div>

{{-- Props Table --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-cog"></i>
            Props
        </h3>
    </div>
    <div class="docs-preview p-0">
        <table class="docs-props-table">
            <thead>
                <tr>
                    <th>Prop</th>
                    <th>Type</th>
                    <th>Default</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>variant</code></td>
                    <td>string</td>
                    <td><code>primary</code></td>
                    <td>primary, secondary, success, warning, danger, outline</td>
                </tr>
                <tr>
                    <td><code>size</code></td>
                    <td>string</td>
                    <td><code>md</code></td>
                    <td>sm, md, lg</td>
                </tr>
                <tr>
                    <td><code>icon</code></td>
                    <td>string</td>
                    <td><code>null</code></td>
                    <td>FontAwesome icon class</td>
                </tr>
                <tr>
                    <td><code>type</code></td>
                    <td>string</td>
                    <td><code>button</code></td>
                    <td>button, submit, reset</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>