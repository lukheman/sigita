@props([
    'title' => 'Modern Admin Dashboard',
    'brandName' => 'AdminPro',
    'brandIcon' => 'fas fa-layer-group'
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    @livewireStyles
    <style>
        :root {
            --sidebar-width: 280px;
            --topbar-height: 70px;
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary-color: #0ea5e9;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);

            /* Light theme (default) */
            --bg-primary: #f1f5f9;
            --bg-secondary: #ffffff;
            --bg-tertiary: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --border-light: #f1f5f9;
            --input-bg: #ffffff;
            --hover-bg: #f8fafc;
        }

        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --border-light: #475569;
            --input-bg: #1e293b;
            --hover-bg: #334155;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.3), 0 1px 2px rgba(0,0,0,0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 1.5rem 1.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand i {
            font-size: 1.8rem;
        }

        .sidebar-menu {
            padding: 1.5rem 0;
        }

        .menu-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            margin: 0.25rem 0.75rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: var(--hover-bg);
            color: var(--primary-color);
        }

        .sidebar-menu a.active {
            background: var(--primary-color);
            color: white;
        }

        .sidebar-menu a i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .topbar {
            background: var(--bg-secondary);
            height: var(--topbar-height);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
            transition: background-color 0.3s ease;
        }

        .topbar .form-control {
            background: var(--input-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        .topbar .form-control::placeholder {
            color: var(--text-muted);
        }

        .topbar .input-group-text {
            background: var(--input-bg);
            border-color: var(--border-color);
        }

        .content-area {
            padding: 2rem;
        }

        .modern-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border-light);
        }

        .modern-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        [data-theme="dark"] .modern-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .stat-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s;
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-color);
        }

        .stat-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-4px);
        }

        [data-theme="dark"] .stat-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .preview-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-light);
        }

        .btn-modern {
            padding: 0.625rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary-modern {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-primary-modern:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .alert-modern {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: start;
            gap: 12px;
        }

        .progress-modern {
            height: 8px;
            border-radius: 50px;
            background: var(--border-light);
        }

        .progress-bar-modern {
            border-radius: 50px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block !important;
            }
        }

        .mobile-toggle {
            display: none;
        }

        .table-modern {
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }

        .table-modern thead th {
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
        }

        .table-modern tbody tr {
            background: var(--bg-secondary);
            box-shadow: var(--card-shadow);
            border-radius: 8px;
        }

        .table-modern tbody td {
            padding: 1rem;
            border: none;
            vertical-align: middle;
            color: var(--text-primary);
        }

        .table-modern tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .table-modern tbody tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Bootstrap Table Dark Mode Override */
        .table {
            --bs-table-bg: var(--bg-secondary);
            --bs-table-color: var(--text-primary);
            --bs-table-border-color: var(--border-color);
            --bs-table-striped-bg: var(--bg-tertiary);
            --bs-table-striped-color: var(--text-primary);
            --bs-table-hover-bg: var(--hover-bg);
            --bs-table-hover-color: var(--text-primary);
        }

        .table > :not(caption) > * > * {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border-bottom-color: var(--border-color);
        }

        .table-modern tbody tr {
            background: var(--bg-secondary) !important;
        }

        .table-modern tbody tr:hover {
            background: var(--hover-bg) !important;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle:hover {
            background: var(--hover-bg);
            color: var(--primary-color);
        }

        .theme-toggle i {
            font-size: 1.25rem;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .topbar .user-name {
            color: var(--text-primary);
        }

        .topbar .user-role {
            color: var(--text-muted);
        }

        /* Modal Styles */
        .modal-backdrop-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content-custom {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border-color);
        }

        .modal-header-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title-custom {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .modal-close-btn:hover {
            color: var(--danger-color);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            background: var(--input-bg);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        /* Input Group Dark Mode */
        .input-group-text {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
        }

        .input-group .form-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .input-group .form-control:focus {
            background: var(--input-bg);
            border-color: var(--primary-color);
            color: var(--text-primary);
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Action buttons in table */
        .action-btn {
            background: transparent;
            border: none;
            padding: 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: var(--hover-bg);
        }

        .action-btn-edit {
            color: var(--primary-color);
        }

        .action-btn-delete {
            color: var(--danger-color);
        }

        /* Pagination */
        .pagination {
            --bs-pagination-bg: var(--bg-secondary);
            --bs-pagination-color: var(--text-primary);
            --bs-pagination-border-color: var(--border-color);
            --bs-pagination-hover-bg: var(--hover-bg);
            --bs-pagination-hover-color: var(--primary-color);
            --bs-pagination-focus-bg: var(--hover-bg);
            --bs-pagination-active-bg: var(--primary-color);
            --bs-pagination-active-border-color: var(--primary-color);
            --bs-pagination-disabled-bg: var(--bg-tertiary);
            --bs-pagination-disabled-color: var(--text-muted);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <x-admin.sidebar :brand-name="$brandName" :brand-icon="$brandIcon">
        <x-admin.sidebar-section title="Main">
            <x-admin.sidebar-link href="{{ route('dashboard') }}" icon="fas fa-home" :active="request()->routeIs('dashboard')">Dashboard</x-admin.sidebar-link>
            <x-admin.sidebar-link href="{{ route('admin.users') }}" icon="fas fa-users" :active="request()->routeIs('admin.users')">Users</x-admin.sidebar-link>
        </x-admin.sidebar-section>

        <x-admin.sidebar-section title="Account">
            <x-admin.sidebar-link href="{{ route('admin.profile') }}" icon="fas fa-user-circle" :active="request()->routeIs('admin.profile')">Profile</x-admin.sidebar-link>
            <x-admin.sidebar-link href="#settings" icon="fas fa-cog">Settings</x-admin.sidebar-link>
        </x-admin.sidebar-section>

        <x-admin.sidebar-section title="Developer">
            <x-admin.sidebar-link href="{{ route('admin.components') }}" icon="fas fa-cubes" :active="request()->routeIs('admin.components')">Components</x-admin.sidebar-link>
        </x-admin.sidebar-section>
    </x-admin.sidebar>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <x-admin.topbar
            :user-name="Auth::user()?->name ?? 'Guest'"
            user-role="Administrator"
            :notification-count="0"
            :show-logout="true"
        />

        <!-- Content Area -->
        <div class="content-area">
            {{ $slot }}
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle Functionality
        function initTheme() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else if (prefersDark) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }

            updateThemeIcon();
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
            }
        }

        // Initialize theme on page load
        initTheme();

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
    </script>
    @livewireScripts
</body>
</html>
