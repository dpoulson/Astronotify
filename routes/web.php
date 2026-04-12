<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/', function () {
    return view('welcome');
});

// Google Socialite Routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/about', function () {
        return view('about');
    })->name('about');

    Route::get('/admin', \App\Livewire\AdminDashboard::class)
        ->middleware('can:admin')
        ->name('admin.dashboard');

    Route::get('/admin/users', \App\Livewire\AdminUsersList::class)
        ->middleware('can:admin')
        ->name('admin.users');

    Route::get('/admin/locations', \App\Livewire\AdminLocationsList::class)
        ->middleware('can:admin')
        ->name('admin.locations');

    Route::get('/admin/users/{user}', \App\Livewire\AdminUserView::class)
        ->middleware('can:admin')
        ->name('admin.user.view');
});
