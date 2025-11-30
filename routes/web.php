<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Admin\{
    Dashboard,
    Users\Index as UsersIndex,
    Promotions\Index as PromotionsIndex,
    Workshops\Index as WorkshopsIndex,
    DataCenter\Index as DataCenterIndex,
    Reports\Index as ReportsIndex,
    Settings\Index as SettingsIndex
};

Route::redirect('/', '/admin/dashboard');
Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

// Grup untuk admin area
Route::middleware(['auth', 'verified'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/admin/users', [Controller::class, 'index'])->name('admin.users');
        Route::get('/users',       UsersIndex::class)->name('users');
        Route::get('/users/create', \App\Livewire\Admin\Users\Create::class)->name('users.create');
        Route::get('/promotions',  PromotionsIndex::class)->name('promotions');
        Route::get('/workshops',   WorkshopsIndex::class)->name('workshops');
        Route::get('/data-center', DataCenterIndex::class)->name('data-center');
        Route::get('/reports',     ReportsIndex::class)->name('reports');
        Route::get('/settings',    SettingsIndex::class)->name('settings');
    });

require __DIR__.'/auth.php';

// Optional fallback
Route::fallback(fn() => abort(404));
Route::get('/json', function () {
    return response()->json([
        'message' => 'BbiHub API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
})->middleware('throttle:60,1'); // Rate limit: 60 requests per minute


// Direct redirect dari root ke admin dashboard
Route::redirect('/', '/admin/dashboard');
Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard.redirect');

/*
|--------------------------------------------------------------------------
| Admin Routes (Superadmin Only)
|--------------------------------------------------------------------------
| 
| These routes require:
| - Authentication (user harus login)
| - Email verification
| - Superadmin role
|
| IMPORTANT: Make sure EnsureSuperadmin middleware is registered first!
| See: app/Http/Middleware/EnsureSuperadmin.php
|      bootstrap/app.php (middleware alias registration)
|
*/
Route::middleware(['auth', 'verified', 'superadmin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', Dashboard::class)
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */
        Volt::route('/profile', 'pages.profile.edit')
            ->name('profile');




        /*
        |--------------------------------------------------------------------------
        | User Management
        |--------------------------------------------------------------------------
        */
        Route::prefix('users')->as('users.')->group(function () {
            Route::get('/', UsersIndex::class)->name('index');
            Route::get('/create', \App\Livewire\Admin\Users\Create::class)->name('create');
            // Future: Add edit, delete routes here
        });

        /*
        |--------------------------------------------------------------------------
        | Promotions Management
        |--------------------------------------------------------------------------
        */
        Route::get('/promotions', PromotionsIndex::class)
            ->name('promotions')
            ->middleware('throttle:120,1'); // 120 requests per minute

        /*
        |--------------------------------------------------------------------------
        | Workshops Management
        |--------------------------------------------------------------------------
        */
        Route::get('/workshops', WorkshopsIndex::class)
            ->name('workshops');

        /*
        |--------------------------------------------------------------------------
        | Data Center
        |--------------------------------------------------------------------------
        */
        Route::get('/data-center', DataCenterIndex::class)
            ->name('data-center')
            ->middleware('throttle:60,1'); // Sensitive data, limit requests

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */
        Route::get('/reports', ReportsIndex::class)
            ->name('reports')
            ->middleware('throttle:30,1'); // Reports might be heavy, limit more

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */
        Route::get('/settings', SettingsIndex::class)
            ->name('settings')
            ->middleware('throttle:60,1'); // Sensitive settings, limit requests
    });

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| 
| Defined in routes/auth.php
| - Login, Register, Password Reset, Email Verification
| - Handled by Livewire Volt components
|
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Fallback Route (404)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    abort(404);
});
