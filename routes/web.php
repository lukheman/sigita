<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\DesaManagement;
use App\Livewire\Admin\BalitaManagement;
use App\Livewire\Admin\PengukuranManagement;
use App\Livewire\Admin\AnalisisKMeans;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\ComponentDocs;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Http\Controllers\Admin\LogoutController;

// Auth Routes
Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');

Route::prefix('admin')->middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // User Management
    Route::get('/users', UserManagement::class)->name('admin.users');

    // Data Master
    Route::get('/desa', DesaManagement::class)->name('admin.desa');
    Route::get('/balita', BalitaManagement::class)->name('admin.balita');
    Route::get('/pengukuran', PengukuranManagement::class)->name('admin.pengukuran');

    // Analisis
    Route::get('/analisis-kmeans', AnalisisKMeans::class)->name('admin.analisis-kmeans');

    // Profile & Components
    Route::get('/profile', Profile::class)->name('admin.profile');
    Route::get('/components', ComponentDocs::class)->name('admin.components');
    Route::post('/logout', [LogoutController::class, '__invoke'])->name('logout');
});