<div class="docs-section-header">
    <h2 class="docs-section-title">
        <i class="fas fa-edit"></i>
        Form Inputs
    </h2>
    <p class="docs-section-desc">Form input components for collecting user data.</p>
</div>

{{-- Text Input --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-i-cursor"></i>
            Text Input
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.input
    label="Email"
    name="email"
    type="email"
    placeholder="Enter your email"
    required
/&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <x-admin.input label="Email" name="email" type="email" placeholder="Enter your email" required />
    </div>
</div>

{{-- Textarea --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-align-left"></i>
            Textarea
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.textarea
    label="Description"
    name="description"
    placeholder="Enter description"
    rows="3"
    hint="Maximum 500 characters"
/&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <x-admin.textarea label="Description" name="description" placeholder="Enter description" rows="3"
            hint="Maximum 500 characters" />
    </div>
</div>

{{-- Date Picker --}}
<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-calendar"></i>
            Date Picker
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.date-picker
    label="Birth Date"
    name="birth_date"
    type="date"
/&gt;

&lt;x-admin.date-picker
    label="Meeting Time"
    name="meeting"
    type="datetime-local"
/&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <div class="row g-3">
            <div class="col-md-6">
                <x-admin.date-picker label="Birth Date" name="birth_date" type="date" />
            </div>
            <div class="col-md-6">
                <x-admin.date-picker label="Meeting Time" name="meeting" type="datetime-local" />
            </div>
        </div>
    </div>
</div>