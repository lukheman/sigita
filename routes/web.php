<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\ComponentDocs;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Http\Controllers\Admin\LogoutController;

// Auth Routes
Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', UserManagement::class)->name('admin.users');
    Route::get('/profile', Profile::class)->name('admin.profile');
    Route::get('/components', ComponentDocs::class)->name('admin.components');
    Route::post('/logout', [LogoutController::class, '__invoke'])->name('logout');
});