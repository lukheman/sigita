<div class="docs-section-header">
    <h2 class="docs-section-title">
        <i class="fas fa-compass"></i>
        Navigation
    </h2>
    <p class="docs-section-desc">Navigation components for guiding users through your app.</p>
</div>

{{-- Breadcrumb --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-angle-double-right"></i>
            Breadcrumb
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.breadcrumb :items="[
    ['label' => 'Home', 'href' => '/', 'icon' => 'fas fa-home'],
    ['label' => 'Users', 'href' => '/users'],
    ['label' => 'Edit User'],
]" /&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <x-admin.breadcrumb :items="[
        ['label' => 'Home', 'href' => '#', 'icon' => 'fas fa-home'],
        ['label' => 'Users', 'href' => '#'],
        ['label' => 'Edit User'],
    ]" />
    </div>
</div>

{{-- Tabs --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-folder"></i>
            Tabs
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;!-- Default tabs --&gt;
&lt;x-admin.tabs&gt;
    &lt;li class="nav-item"&gt;&lt;a class="nav-link active" href="#"&gt;Profile&lt;/a&gt;&lt;/li&gt;
    &lt;li class="nav-item"&gt;&lt;a class="nav-link" href="#"&gt;Settings&lt;/a&gt;&lt;/li&gt;
&lt;/x-admin.tabs&gt;

&lt;!-- Pills variant --&gt;
&lt;x-admin.tabs variant="pills"&gt;
    &lt;li class="nav-item"&gt;&lt;a class="nav-link active" href="#"&gt;Profile&lt;/a&gt;&lt;/li&gt;
    &lt;li class="nav-item"&gt;&lt;a class="nav-link" href="#"&gt;Settings&lt;/a&gt;&lt;/li&gt;
&lt;/x-admin.tabs&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="mb-4">
            <small class="text-muted d-block mb-2">Default:</small>
            <x-admin.tabs>
                <li class="nav-item"><a class="nav-link active" href="#">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Security</a></li>
            </x-admin.tabs>
        </div>
        <div>
            <small class="text-muted d-block mb-2">Pills:</small>
            <x-admin.tabs variant="pills">
                <li class="nav-item"><a class="nav-link active" href="#">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Security</a></li>
            </x-admin.tabs>
        </div>
    </div>
</div>