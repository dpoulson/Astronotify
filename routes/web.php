<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/notifications/manage/{user}', \App\Livewire\ManageNotifications::class)
    ->name('notifications.manage');

if (app()->environment('local', 'testing')) {
    Route::get('/login-as-test-user', function () {
        auth()->loginUsingId(1);
        return redirect('/dashboard');
    });
}

// Google Socialite Routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');

Route::get('/help', function () {
    return redirect()->route('faq');
})->name('help');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/privacy-policy', function () {
    return redirect()->route('privacy');
})->name('policy.show');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

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

    Route::get('/admin/settings', \App\Livewire\AdminSettings::class)
        ->middleware('can:admin')
        ->name('admin.settings');

    Route::get('/admin/queue', \App\Livewire\AdminEmailQueue::class)
        ->middleware('can:admin')
        ->name('admin.queue');

    Route::get('/admin/crons', \App\Livewire\AdminCronMonitor::class)
        ->middleware('can:admin')
        ->name('admin.crons');
});
