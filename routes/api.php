<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Owner\EmployementApiController;
use App\Http\Controllers\Owner\WorkshopApiController;
use App\Http\Controllers\Owner\WorkshopDocumentApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik (Tidak Perlu Login)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rute Terlindungi (Wajib Login / Bawa Token Sanctum)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', function (Request $request) {
        return $request->user()->load('roles:name');
    });

    /*
    |--------------------------------------------------------------------------
    | Grup Rute untuk 'Owner'
    | (Nanti bisa Anda tambahkan middleware 'role:owner')
    |--------------------------------------------------------------------------
    */
    Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
        Route::prefix('owners')->group(function () {
            // --- Rute Workshop (Step 1) ---
            Route::post('workshops', [WorkshopApiController::class, 'store']);

            // --- Rute Dokumen (Step 2) ---
            Route::post('documents', [WorkshopDocumentApiController::class, 'store']);

            // --- Rute Employee ---
            Route::apiResource('employee', EmployementApiController::class);

            // --- Rute Customer ---
            Route::apiResource('customer', CustomerApiController::class);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Grup Rute untuk 'Technician' (Contoh)
    |--------------------------------------------------------------------------
    */
    // Route::prefix('technician')->middleware('role:technician')->group(function () {
    //    Route::get('workshops/{workshop}', [WorkshopTechnicianController::class, 'show']);
    // });
});
