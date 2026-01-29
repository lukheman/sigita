<div class="docs-section-header">
    <h2 class="docs-section-title">
        <i class="fas fa-list"></i>
        Select & Dropdown
    </h2>
    <p class="docs-section-desc">Selection components for choosing from multiple options.</p>
</div>

{{-- Select --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-caret-square-down"></i>
            Select
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.select
    label="Country"
    name="country"
    :options="['id' => 'Indonesia', 'my' => 'Malaysia', 'sg' => 'Singapore']"
    placeholder="Select country"
/&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <x-admin.select label="Country" name="country" :options="['id' => 'Indonesia', 'my' => 'Malaysia', 'sg' => 'Singapore']" placeholder="Select country" />
    </div>
</div>

{{-- Search Select --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-search"></i>
            Search Select
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.search-select
    label="City"
    name="city"
    :options="['jkt' => 'Jakarta', 'sby' => 'Surabaya', 'bdg' => 'Bandung', 'mdn' => 'Medan']"
    placeholder="Search city..."
/&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <x-admin.search-select label="City" name="city" :options="['jkt' => 'Jakarta', 'sby' => 'Surabaya', 'bdg' => 'Bandung', 'mdn' => 'Medan']" placeholder="Search city..." />
    </div>
</div>

{{-- Dropdown --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-ellipsis-v"></i>
            Dropdown Menu
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.dropdown&gt;
    &lt;x-slot:trigger&gt;
        &lt;x-admin.button variant="primary"&gt;
            Actions &lt;i class="fas fa-chevron-down ms-2"&gt;&lt;/i&gt;
        &lt;/x-admin.button&gt;
    &lt;/x-slot:trigger&gt;

    &lt;li&gt;&lt;a class="dropdown-item" href="#"&gt;&lt;i class="fas fa-edit"&gt;&lt;/i&gt; Edit&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a class="dropdown-item" href="#"&gt;&lt;i class="fas fa-copy"&gt;&lt;/i&gt; Duplicate&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;hr class="dropdown-divider"&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a class="dropdown-item text-danger" href="#"&gt;&lt;i class="fas fa-trash"&gt;&lt;/i&gt; Delete&lt;/a&gt;&lt;/li&gt;
&lt;/x-admin.dropdown&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <x-admin.dropdown>
            <x-slot:trigger>
                <x-admin.button variant="primary">
                    Actions <i class="fas fa-chevron-down ms-2"></i>
                </x-admin.button>
            </x-slot:trigger>

            <li><a class="dropdown-item" href="#"><i class="fas fa-edit"></i> Edit</a></li>
            <li><a class="dropdown-item" href="#"><i class="fas fa-copy"></i> Duplicate</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash"></i> Delete</a></li>
        </x-admin.dropdown>
    </div>
</div>