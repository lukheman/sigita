<div class="docs-section-header">
    <h2 class="docs-section-title">
        <i class="fas fa-image"></i>
        Media & Upload
    </h2>
    <p class="docs-section-desc">Components for displaying and uploading media.</p>
</div>

{{-- Avatar --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-user-circle"></i>
            Avatar
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.avatar name="John Doe" size="sm" /&gt;
&lt;x-admin.avatar name="Jane Smith" size="md" status="online" /&gt;
&lt;x-admin.avatar name="Admin User" size="lg" status="busy" /&gt;
&lt;x-admin.avatar src="/path/to/image.jpg" size="xl" /&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="text-center">
                <x-admin.avatar name="JD" size="xs" />
                <div class="mt-1"><small class="text-muted">xs</small></div>
            </div>
            <div class="text-center">
                <x-admin.avatar name="Jane Smith" size="sm" status="online" />
                <div class="mt-1"><small class="text-muted">sm + online</small></div>
            </div>
            <div class="text-center">
                <x-admin.avatar name="Admin User" size="md" status="away" />
                <div class="mt-1"><small class="text-muted">md + away</small></div>
            </div>
            <div class="text-center">
                <x-admin.avatar name="Test User" size="lg" status="busy" />
                <div class="mt-1"><small class="text-muted">lg + busy</small></div>
            </div>
            <div class="text-center">
                <x-admin.avatar name="XL Avatar" size="xl" status="offline" />
                <div class="mt-1"><small class="text-muted">xl + offline</small></div>
            </div>
        </div>
    </div>
</div>

{{-- Avatar Props --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-cog"></i>
            Avatar Props
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
                    <td><code>src</code></td>
                    <td>string</td>
                    <td><code>null</code></td>
                    <td>Image URL for avatar</td>
                </tr>
                <tr>
                    <td><code>name</code></td>
                    <td>string</td>
                    <td><code>null</code></td>
                    <td>Name for generating initials (fallback)</td>
                </tr>
                <tr>
                    <td><code>size</code></td>
                    <td>string</td>
                    <td><code>md</code></td>
                    <td>xs, sm, md, lg, xl</td>
                </tr>
                <tr>
                    <td><code>status</code></td>
                    <td>string</td>
                    <td><code>null</code></td>
                    <td>online, offline, away, busy</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- File Upload --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-cloud-upload-alt"></i>
            File Upload
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.file-upload
    label="Upload Image"
    name="image"
    accept="image/*"
    maxSize="2MB"
/&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <x-admin.file-upload label="Upload Image" name="image" accept="image/*" maxSize="2MB" />
    </div>
</div>