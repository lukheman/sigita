# Laravel Admin Dashboard Template

A modern, elegant admin dashboard template built with Laravel 12, Livewire, and Tailwind CSS. Features a dark mode design with reusable components and user management capabilities.

## ✨ Features

- 🎨 **Modern Dark UI** - Beautiful, modern dark theme with glassmorphism effects
- ⚡ **Livewire Components** - Dynamic, reactive components without writing JavaScript
- 🔐 **Authentication** - Modern login system with form validation
- 👥 **User Management** - Full CRUD operations for managing users
- 🧩 **Reusable Components** - Extensive library of Blade components for rapid development
- 📱 **Responsive Design** - Fully responsive layout that works on all devices
- 🎯 **Component Library** - Pre-built UI components including:
  - Buttons, Inputs, Badges, Alerts
  - Tables with sorting and pagination
  - Modals, Cards, and Progress Bars
  - Sidebar navigation with sections
  - Topbar with user menu

## 🚀 Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Livewire, Tailwind CSS 4.x
- **Build Tool**: Vite 7.x
- **Testing**: Pest PHP

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js & npm
- MySQL/PostgreSQL/SQLite

## 🛠️ Installation

### Quick Setup

Run the automated setup script:

```bash
composer setup
```

This will:
- Install PHP dependencies
- Create `.env` file from `.env.example`
- Generate application key
- Run database migrations
- Install npm dependencies
- Build frontend assets

### Manual Setup

If you prefer to set up manually:

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd new-admin-template
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Install npm dependencies and build assets**
   ```bash
   npm install
   npm run build
   ```

## 🏃 Running the Application

### Development Mode

The easiest way to run the application in development mode:

```bash
composer dev
```

This command starts:
- Laravel development server (http://localhost:8000)
- Queue worker
- Laravel Pail (logs)
- Vite dev server (hot module replacement)

All services run concurrently with color-coded output.

### Individual Services

Alternatively, run services separately:

```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Vite dev server
npm run dev

# Terminal 3 (optional) - Queue worker
php artisan queue:listen

# Terminal 4 (optional) - Logs
php artisan pail
```

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/Admin/    # Admin controllers
│   └── Livewire/                  # Livewire components
│       ├── Admin/                 # Admin Livewire components
│       │   └── UserManagement.php # User CRUD component
│       └── Auth/                  # Auth Livewire components
│           └── Login.php          # Login component
├── resources/
│   ├── css/                       # Global styles
│   ├── js/                        # JavaScript files
│   └── views/
│       ├── admin/                 # Admin views
│       ├── components/            # Reusable Blade components
│       │   ├── admin/             # Admin UI components
│       │   └── auth/              # Auth UI components
│       └── livewire/              # Livewire views
├── routes/
│   └── web.php                    # Web routes
└── database/
    └── migrations/                # Database migrations
```

## 🎨 Available Components

### Admin Components

Located in `resources/views/components/admin/`:

- **Layout Components**
  - `layout.blade.php` - Main admin layout wrapper
  - `livewire-layout.blade.php` - Layout for Livewire pages
  - `sidebar.blade.php` - Sidebar navigation
  - `topbar.blade.php` - Top navigation bar

- **Navigation Components**
  - `sidebar-link.blade.php` - Sidebar navigation links
  - `sidebar-section.blade.php` - Sidebar section headers

- **UI Components**
  - `button.blade.php` - Reusable button component
  - `input.blade.php` - Form input component
  - `badge.blade.php` - Status badges
  - `alert.blade.php` - Alert messages
  - `confirm-modal.blade.php` - Confirmation dialogs
  - `progress-bar.blade.php` - Progress indicators

- **Card Components**
  - `modern-card.blade.php` - Modern styled cards
  - `feature-card.blade.php` - Feature display cards
  - `stat-card.blade.php` - Statistics cards
  - `table-card.blade.php` - Table wrapper cards
  - `page-header.blade.php` - Page header sections

- **Data Components**
  - `table.blade.php` - Data tables with sorting

### Auth Components

Located in `resources/views/components/auth/`:

- `layout.blade.php` - Authentication layout wrapper

## 🔑 Authentication

The application includes a modern login system:

- **Route**: `/login`
- **Component**: `App\Livewire\Auth\Login`
- **View**: `resources/views/livewire/auth/login.blade.php`

Protected admin routes require authentication middleware.

## 👥 User Management

Full CRUD interface for managing users:

- **Route**: `/admin/users`
- **Component**: `App\Livewire\Admin\UserManagement`
- **Features**:
  - Create new users
  - Edit existing users
  - Delete users
  - Search and filter
  - Pagination

## 🧪 Testing

Run the test suite:

```bash
composer test
# or
php artisan test
```

## 🎯 Usage Examples

### Creating a New Admin Page

1. Create a Livewire component:
   ```bash
   php artisan make:livewire Admin/YourComponent
   ```

2. Add a route in `routes/web.php`:
   ```php
   Route::get('/admin/your-page', YourComponent::class)
       ->name('admin.your-page')
       ->middleware('auth');
   ```

3. Use the admin layout in your view:
   ```blade
   <x-admin.livewire-layout>
       <x-admin.page-header 
           title="Your Page Title"
           description="Page description"
       />
       
       <x-admin.modern-card>
           <!-- Your content -->
       </x-admin.modern-card>
   </x-admin.livewire-layout>
   ```

### Using Component Examples

**Button Component:**
```blade
<x-admin.button type="primary" size="md">
    Save Changes
</x-admin.button>
```

**Input Component:**
```blade
<x-admin.input 
    label="Email"
    type="email"
    name="email"
    required
/>
```

**Alert Component:**
```blade
<x-admin.alert type="success">
    Operation completed successfully!
</x-admin.alert>
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License.

## 🆘 Support

For issues, questions, or contributions, please open an issue on GitHub.

---

**Built with ❤️ using Laravel and Livewire**
