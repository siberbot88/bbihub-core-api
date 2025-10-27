<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\Owner\EmployementApiController;
use App\Http\Controllers\Api\Owner\WorkshopApiController;
use App\Http\Controllers\Api\Owner\WorkshopDocumentApiController;
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
    Route::prefix('owners')->middleware('auth:sanctum')->group(function () {
        // --- Rute Workshop (Step 1) ---
        Route::post('workshops', [WorkshopApiController::class, 'store']);

        // --- Rute Dokumen (Step 2) ---
        Route::post('documents', [WorkshopDocumentApiController::class, 'store']);

        // --- Rute Employee ---
        Route::apiResource('employee', EmployementApiController::class);

        // --- Rute Customer ---
        Route::apiResource('workshop', WorkshopApiController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Grup Rute untuk 'Mechanic'
    |--------------------------------------------------------------------------
    */
     Route::prefix('mechanics')->middleware('role:technician')->group(function () {
        //content api mechanic
     });


    /*
   |--------------------------------------------------------------------------
   | Grup Rute untuk 'Admin'
   |--------------------------------------------------------------------------
   */
    Route::prefix('admins')->middleware('role:technician')->group(function () {

    });
});
