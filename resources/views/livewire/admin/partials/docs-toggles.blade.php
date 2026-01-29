<div class="docs-section-header">
    <h2 class="docs-section-title">
        <i class="fas fa-toggle-on"></i>
        Toggle & Checkbox
    </h2>
    <p class="docs-section-desc">Toggle switches and selection controls.</p>
</div>

{{-- Toggle --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-toggle-on"></i>
            Toggle Switch
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.toggle label="Enable notifications" name="notifications" /&gt;
&lt;x-admin.toggle label="Dark mode" name="dark" checked /&gt;
&lt;x-admin.toggle onLabel="On" offLabel="Off" name="status" size="lg" /&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="d-flex flex-column gap-3">
            <x-admin.toggle label="Enable notifications" name="notifications" />
            <x-admin.toggle label="Dark mode" name="dark" checked />
            <x-admin.toggle onLabel="On" offLabel="Off" name="status" size="lg" />
        </div>
    </div>
</div>

{{-- Checkbox --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-check-square"></i>
            Checkbox
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.checkbox label="Remember me" name="remember" /&gt;
&lt;x-admin.checkbox label="I agree to terms" name="terms" checked /&gt;
&lt;x-admin.checkbox label="Disabled option" name="disabled" disabled /&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="d-flex flex-column gap-2">
            <x-admin.checkbox label="Remember me" name="remember" />
            <x-admin.checkbox label="I agree to terms" name="terms" checked />
            <x-admin.checkbox label="Disabled option" name="disabled" disabled />
        </div>
    </div>
</div>

{{-- Radio --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-dot-circle"></i>
            Radio Buttons
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.radio label="Option 1" name="option" value="1" checked /&gt;
&lt;x-admin.radio label="Option 2" name="option" value="2" /&gt;
&lt;x-admin.radio label="Option 3" name="option" value="3" /&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="d-flex flex-column gap-2">
            <x-admin.radio label="Option 1" name="option" value="1" checked />
            <x-admin.radio label="Option 2" name="option" value="2" />
            <x-admin.radio label="Option 3" name="option" value="3" />
        </div>
    </div>
</div>