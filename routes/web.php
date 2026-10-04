<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\SitemapController;
use App\Livewire\AdminCronMonitor;
use App\Livewire\AdminDashboard;
use App\Livewire\AdminEmailQueue;
use App\Livewire\AdminFeedbackRequests;
use App\Livewire\AdminLocationsList;
use App\Livewire\AdminSettings;
use App\Livewire\AdminStargazingSpots;
use App\Livewire\AdminUsersList;
use App\Livewire\AdminUserView;
use App\Livewire\ManageNotifications;
use App\Livewire\PublicSpotsList;
use App\Livewire\PublicSpotView;
use App\Livewire\PublicTransitView;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/notifications/manage/{user}', ManageNotifications::class)
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

// Public Marketing & SEO Routes
Route::get('/sitemap.xml', SitemapController::class.'@index')->name('sitemap');
Route::get('/spots', PublicSpotsList::class)->name('spots.index');
Route::get('/spots/{slug}', PublicSpotView::class)->name('spots.show');
Route::get('/transit/{token}', PublicTransitView::class)->name('transit.show');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/admin', AdminDashboard::class)
        ->middleware('can:admin')
        ->name('admin.dashboard');

    Route::get('/admin/users', AdminUsersList::class)
        ->middleware('can:admin')
        ->name('admin.users');

    Route::get('/admin/locations', AdminLocationsList::class)
        ->middleware('can:admin')
        ->name('admin.locations');

    Route::get('/admin/spots', AdminStargazingSpots::class)
        ->middleware('can:admin')
        ->name('admin.spots');

    Route::get('/admin/requests', AdminFeedbackRequests::class)
        ->middleware('can:admin')
        ->name('admin.requests');

    Route::get('/admin/users/{user}', AdminUserView::class)
        ->middleware('can:admin')
        ->name('admin.user.view');

    Route::get('/admin/settings', AdminSettings::class)
        ->middleware('can:admin')
        ->name('admin.settings');

    Route::get('/admin/queue', AdminEmailQueue::class)
        ->middleware('can:admin')
        ->name('admin.queue');

    Route::get('/admin/crons', AdminCronMonitor::class)
        ->middleware('can:admin')
        ->name('admin.crons');
});
