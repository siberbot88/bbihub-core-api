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

// Redirect dari root ke dashboard
Route::redirect('/', '/admin/dashboard');
Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');

// Grup untuk admin area
Route::middleware(['auth', 'verified'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/admin/users', [Controller::class, 'index'])->name('admin.users');
        Route::get('/users',       UsersIndex::class)->name('users');
        Route::get('/promotions',  PromotionsIndex::class)->name('promotions');
        Route::get('/workshops',   WorkshopsIndex::class)->name('workshops');
        Route::get('/data-center', DataCenterIndex::class)->name('data-center');
        Route::get('/reports',     ReportsIndex::class)->name('reports');
        Route::get('/settings',    SettingsIndex::class)->name('settings');
    });

require __DIR__.'/auth.php';

// Optional fallback
Route::fallback(fn() => abort(404));
