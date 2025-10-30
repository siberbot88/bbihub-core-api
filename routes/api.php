<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\Owner\EmployementApiController;
use App\Http\Controllers\Api\Owner\WorkshopApiController;
use App\Http\Controllers\Api\Owner\WorkshopDocumentApiController;
use App\Http\Controllers\Api\ServiceApiContoller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik (Tidak Perlu Login)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.login');
});

/*
|--------------------------------------------------------------------------
| Rute Terlindungi (Wajib Login / Bawa Token Sanctum)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('auth/user', function (Request $request) {
        $user = $request->user();
        if ($user->hasRole('owner')) {
            $user->load('workshops');
        } else {
            $user->load('employment.workshop');
        }
        $user->load('roles:name');
        return response()->json($user);
    })->name('api.user');

    /*
    |--------------------------------------------------------------------------
    | Grup Rute untuk 'Owner'
    |--------------------------------------------------------------------------
    */

    Route::prefix('owners')->middleware('role:owner')->name('api.owner.')->group(function () {
        // --- Rute Workshop ---
        Route::post('workshops', [WorkshopApiController::class, 'store'])->name('workshops.store');
        Route::get('workshops', [WorkshopApiController::class, 'index'])->name('workshops.index');
        Route::get('workshops/{workshop}', [WorkshopApiController::class, 'show'])->name('workshops.show');
        Route::put('workshops/{workshop}', [WorkshopApiController::class, 'update'])->name('workshops.update');

        // --- Rute Dokumen ---
        Route::post('documents', [WorkshopDocumentApiController::class, 'store'])->name('documents.store');
        Route::get('documents', [WorkshopDocumentApiController::class, 'index'])->name('documents.index');


        // --- Rute Employee ---
        Route::get('employee', [EmployementApiController::class, 'index'])->name('employee.index');
        Route::post('employee', [EmployementApiController::class, 'store'])->name('employee.store');
        Route::get('employee/{employee}', [EmployementApiController::class, 'show'])->name('employee.show');
        Route::put('employee/{employee}', [EmployementApiController::class, 'update'])->name('employee.update');
        Route::delete('employee/{employee}', [EmployementApiController::class, 'destroy'])->name('employee.destroy');

        // --- Rute Customer (Jika Owner perlu akses) ---
         Route::apiResource('customers', CustomerApiController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Grup Rute untuk 'Mechanic'
    |--------------------------------------------------------------------------
    */
    // Menggunakan middleware 'role:mechanic' dari Spatie
    Route::prefix('mechanics')->middleware('role:mechanic')->name('api.mechanic.')->group(function () {

    });

    /*
    |--------------------------------------------------------------------------
    | Grup Rute untuk 'Admin'
    |--------------------------------------------------------------------------
    */
    // Menggunakan middleware 'role:admin' dari Spatie
    Route::prefix('admins')->middleware('role:admin')->name('api.admin.')->group(function () {
        // --- Contoh Rute Admin ---
        // Route::get('reports', [AdminReportController::class, 'index']);
    });
});

/*
|--------------------------------------------------------------------------
| Rute Lain (Keperluan Tes / Publik?)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () { // Mungkin maksudnya '/services' saja?
    Route::get('services', [ServiceApiContoller::class, 'index']);
    Route::post('services', [ServiceApiContoller::class, 'store']);
    Route::get('services/{service}', [ServiceApiContoller::class, 'show']);
});

