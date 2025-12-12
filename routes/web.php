<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Livewire Admin Components
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\Workshops\Index as WorkshopsIndex;
use App\Livewire\Admin\DataCenter\Index as DataCenterIndex;
use App\Livewire\Admin\Reports\Index as ReportsIndex;
use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Admin\Promotions\Index as PromotionsIndex;
use App\Livewire\Admin\Promotions\Create as PromotionCreate;

/*
|--------------------------------------------------------------------------
| Redirects utama
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/admin/dashboard');
Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard.redirect');

/*
|--------------------------------------------------------------------------
| JSON healthcheck (opsional)
|--------------------------------------------------------------------------
*/

Route::get('/json', function () {
    return response()->json([
        'message' => 'BbiHub API',
        'version' => '1.0.0',
        'status'  => 'running',
    ]);
})->middleware('throttle:60,1');

/*
|--------------------------------------------------------------------------
| Admin Routes (Superadmin Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'superadmin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', Dashboard::class)
            ->name('dashboard');

        // Profile (Volt)
        Volt::route('/profile', 'pages.profile.edit')
            ->name('profile');

        // Users
        Route::prefix('users')->as('users.')->group(function () {
            Route::get('/', UsersIndex::class)->name('index');
            Route::get('/create', \App\Livewire\Admin\Users\Create::class)->name('create');
        });

        // Promotions
        Route::prefix('promotions')->as('promotions.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Promotions\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Admin\Promotions\Create::class)->name('create');
        });

        // Workshops
        Route::get('/workshops', WorkshopsIndex::class)
            ->name('workshops');

        // Data Center
        Route::get('/data-center', DataCenterIndex::class)
            ->name('data-center')
            ->middleware('throttle:60,1');

        // Data Center - create
        Route::get('/data-center/create', \App\Livewire\Admin\DataCenter\Create::class)
            ->name('data-center.create');
        // Data Center - edit
        Route::get('/data-center/edit', \App\Livewire\Admin\DataCenter\Edit::class)
            ->name('data-center.edit');

        // Reports
        Route::get('/reports', ReportsIndex::class)
            ->name('reports')
            ->middleware('throttle:30,1');

        // Settings
        Route::get('/settings', SettingsIndex::class)
            ->name('settings')
            ->middleware('throttle:60,1');
    });

require __DIR__.'/auth.php';

Route::fallback(function () {
    abort(404);
});
