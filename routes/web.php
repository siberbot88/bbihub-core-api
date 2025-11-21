<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them
| will be assigned to the "web" middleware group.
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'BbiHub API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});
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
