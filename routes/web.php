<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\{
    Dashboard,
    Users\Index as UsersIndex,
    Promotions\Index as PromotionsIndex,
    Workshops\Index as WorkshopsIndex,
    DataCenter\Index as DataCenterIndex,
    Reports\Index as ReportsIndex,
    Settings\Index as SettingsIndex
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/json', function () {
    return response()->json([
        'message' => 'BbiHub API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

// Redirect root ke dashboard admin
Route::redirect('/', '/admin/dashboard');
Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

// GROUP ADMIN
Route::middleware(['auth', 'verified'])
    ->prefix('admin')->as('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', Dashboard::class)->name('dashboard');

        // Users
        Route::get('/users', UsersIndex::class)->name('users');
        Route::get('/users/create', \App\Livewire\Admin\Users\Create::class)->name('users.create');

        // Promotions
        Route::get('/promotions', PromotionsIndex::class)->name('promotions');

        // Workshops
        Route::get('/workshops', WorkshopsIndex::class)->name('workshops');

        // Data Center
        Route::get('/data-center', DataCenterIndex::class)->name('data-center');

        // Reports
        Route::get('/reports', ReportsIndex::class)->name('reports');

        // Settings
        Route::get('/settings', SettingsIndex::class)->name('settings');
    });

// Auth scaffolding (Livewire Volt)
require __DIR__.'/auth.php';

Route::fallback(fn() => abort(404));
