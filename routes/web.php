<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Livewire Admin Components
use App\Livewire\Admin\Dashboard;

// Users
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\Users\Create as UsersCreate;
use App\Livewire\Admin\Users\Edit as UsersEdit;

// Workshops
use App\Livewire\Admin\Workshops\Index as WorkshopsIndex;
use App\Livewire\Admin\Workshops\Edit as WorkshopsEdit;

// Data Center
use App\Livewire\Admin\DataCenter\Index as DataCenterIndex;

// Reports & Settings
use App\Livewire\Admin\Reports\Index as ReportsIndex;
use App\Livewire\Admin\Settings\Index as SettingsIndex;

// Promotions
use App\Livewire\Admin\Promotions\Index as PromotionsIndex;
use App\Livewire\Admin\Promotions\Create as PromotionCreate;
use App\Livewire\Admin\Promotions\Edit as PromotionEdit;

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
        'status' => 'running',
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

        // Executive EIS
        Route::get('/executive-dashboard', \App\Livewire\Admin\ExecutiveDashboard::class)
            ->name('executive-dashboard');

        Route::get('/eis/print', [\App\Http\Controllers\Admin\EisReportController::class, 'print'])
            ->name('eis.print');

        // Profile (Volt)
        Volt::route('/profile', 'pages.profile.edit')
            ->name('profile');

        /*
        |--------------------------
        | Users
        |--------------------------
        |
        | Nama route:
        | - admin.users.index
        | - admin.users.create
        | - admin.users.edit
        */
        Route::prefix('users')->as('users.')->group(function () {
            Route::get('/', UsersIndex::class)
                ->name('index');

            Route::get('/create', UsersCreate::class)
                ->name('create');

            Route::get('/{user}/edit', UsersEdit::class)
                ->name('edit');
        });

        /*
        |--------------------------
        | Promotions
        |--------------------------
        |
        | Nama route:
        | - admin.promotions.index
        | - admin.promotions.create
        | - admin.promotions.edit
        */
        Route::prefix('promotions')->as('promotions.')->group(function () {
            Route::get('/', PromotionsIndex::class)
                ->name('index');

            Route::get('/create', PromotionCreate::class)
                ->name('create');

            Route::get('/{promotion}/edit', PromotionEdit::class)
                ->name('edit');
        });

        /*
        |--------------------------
        | Workshops
        |--------------------------
        |
        | Nama route:
        | - admin.workshops.verification
        | - admin.workshops.index
        | - admin.workshops.edit
        */
        Route::prefix('workshops')->as('workshops.')->group(function () {
            // Verification Route
            Route::get('/verification', \App\Livewire\Admin\Workshops\Verification::class)
                ->name('verification');

            Route::get('/', WorkshopsIndex::class)
                ->name('index');

            Route::get('/{workshop}/edit', WorkshopsEdit::class)
                ->name('edit');
        });

        /*
        |--------------------------
        | Data Center
        |--------------------------
        |
        | Nama route:
        | - admin.data-center
        */
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

        /*
        |--------------------------
        | Settings
        |--------------------------
        */
        Route::get('/settings', SettingsIndex::class)
            ->name('settings')
            ->middleware('throttle:60,1');

        /*
        |--------------------------
        | Demo Form (Testing Tool)
        |--------------------------
        */
        Route::get('/demo-form', \App\Livewire\Admin\Demo\Form::class)
            ->name('demo-form');
    });

require __DIR__ . '/auth.php';

Route::fallback(function () {
    abort(404);
});
