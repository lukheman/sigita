<div class="docs-section-header">
    <h2 class="docs-section-title">
        <i class="fas fa-table"></i>
        Tables
    </h2>
    <p class="docs-section-desc">Table components for displaying structured data.</p>
</div>

<div class="docs-card">
    <div class="docs-card-header">
        <h3 class="docs-card-title">
            <i class="fas fa-th-list"></i>
            Table Card
        </h3>
        <div class="docs-card-actions">
            <button class="docs-copy-btn" onclick="copyCode(this)">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>
    <div class="docs-code">
        <pre><code>&lt;x-admin.table-card title="Recent Orders" :headers="['ID', 'Customer', 'Amount', 'Status']"&gt;
    &lt;tr&gt;
        &lt;td&gt;#001&lt;/td&gt;
        &lt;td&gt;John Doe&lt;/td&gt;
        &lt;td&gt;$129.99&lt;/td&gt;
        &lt;td&gt;&lt;x-admin.badge variant="success"&gt;Completed&lt;/x-admin.badge&gt;&lt;/td&gt;
    &lt;/tr&gt;
&lt;/x-admin.table-card&gt;</code></pre>
    </div>
    <div class="docs-preview">
        <div class="docs-preview-label">Live Preview</div>
        <x-admin.table-card title="Recent Orders" :headers="['ID', 'Customer', 'Amount', 'Status']">
            <tr>
                <td><strong style="color: var(--text-primary);">#001</strong></td>
                <td>John Doe</td>
                <td>$129.99</td>
                <td><x-admin.badge variant="success" icon="fas fa-check">Completed</x-admin.badge></td>
            </tr>
            <tr>
                <td><strong style="color: var(--text-primary);">#002</strong></td>
                <td>Jane Smith</td>
                <td>$89.50</td>
                <td><x-admin.badge variant="warning" icon="fas fa-clock">Pending</x-admin.badge></td>
            </tr>
            <tr>
                <td><strong style="color: var(--text-primary);">#003</strong></td>
                <td>Robert Johnson</td>
                <td>$249.00</td>
                <td><x-admin.badge variant="primary" icon="fas fa-truck">Shipping</x-admin.badge></td>
            </tr>
        </x-admin.table-card>
    </div>
</div>