<div class="docs-section-header">
    <h2 class="docs-section-title">
        <i class="fas fa-square"></i>
        Cards
    </h2>
    <p class="docs-section-desc">Card components for displaying content in various formats.</p>
</div>

{{-- Stat Card --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-chart-line"></i>
            Stat Card
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.stat-card
    icon="fas fa-users"
    label="Total Users"
    value="1,234"
    trend-value="12% from last month"
    trend-direction="up"
    variant="primary"
/&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="row g-3">
            <div class="col-md-4">
                <x-admin.stat-card icon="fas fa-users" label="Total Users" value="1,234"
                    trend-value="12% from last month" trend-direction="up" variant="primary" />
            </div>
            <div class="col-md-4">
                <x-admin.stat-card icon="fas fa-dollar-sign" label="Revenue" value="$48,574"
                    trend-value="8% from last month" trend-direction="up" variant="success" />
            </div>
            <div class="col-md-4">
                <x-admin.stat-card icon="fas fa-chart-pie" label="Conversion" value="3.24%"
                    trend-value="2% from last month" trend-direction="down" variant="warning" />
            </div>
        </div>
    </div>
</div>

{{-- Feature Card --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-star"></i>
            Feature Card
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.feature-card
    icon="fas fa-rocket"
    title="Fast Performance"
    description="Lightning fast load times"
    variant="primary"
/&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="row g-3">
            <div class="col-md-4">
                <x-admin.feature-card icon="fas fa-rocket" title="Fast Performance"
                    description="Lightning fast load times" variant="primary" />
            </div>
            <div class="col-md-4">
                <x-admin.feature-card icon="fas fa-shield-alt" title="Secure" description="Enterprise-grade security"
                    variant="success" />
            </div>
            <div class="col-md-4">
                <x-admin.feature-card icon="fas fa-mobile-alt" title="Responsive" description="Perfect on any device"
                    variant="secondary" />
            </div>
        </div>
    </div>
</div>

{{-- Modern Card --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-layer-group"></i>
            Modern Card
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.modern-card&gt;
    Your content here...
&lt;/x-admin.modern-card&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <x-admin.modern-card>
            <p class="mb-0" style="color: var(--text-primary);">This is a modern card with beautiful styling and hover
                effects. Perfect for any content type.</p>
        </x-admin.modern-card>
    </div>
</div>