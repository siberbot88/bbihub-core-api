<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Owner\EmployementApiController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rute Publik untuk Otentikasi
Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Rute yang Dilindungi (Perlu Login / Token Sanctum)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Anda bisa tambahkan middleware Spatie di sini nanti: ->middleware('role:owner')
    Route::prefix('owners')->group(function () {

        // Rute Workshop (Contoh)
        // Route::get('workshops', [WorkshopController::class, 'index']);
        // Route::post('workshops', [WorkshopController::class, 'store']);
        // Route::put('workshops/{workshop}', [WorkshopController::class, 'update']);

        // Rute Employee (Contoh dari file Anda sebelumnya)
        // Route::get('employee', [EmployementApiController::class, 'index']);
        // Route::get('employee/{employment}', [EmployementApiController::class, 'show']);
        // Route::post('employee', [EmployementApiController::class, 'store']);
        // Route::put('employee/{employment}', [EmployementApiController::class, 'update']);
        // Route::delete('employee/{employment}', [EmployementApiController::class, 'destroy']);

        // Rute Customer (Contoh dari file Anda sebelumnya)
        Route::apiResource('customer', CustomerApiController::class);
    });

    // Anda bisa tambahkan grup rute lain untuk 'admin' atau 'mechanic' di sini
    // Route::prefix('mechanic')->middleware('role:mechanic')->group(function () {
    //    ...
    // });

});
//middleware('auth:sanctum')->

Route::prefix('v1')->group(function () {
    Route::prefix('owner')->group(function () {
        Route::get('workshops', [WorkshopController::class, 'index']);
        Route::post('workshops', [WorkshopController::class, 'store']);
        Route::put('workshops/{workshop}', [WorkshopController::class, 'update']);

        //Employee
        Route::get('employee', [EmployementApiController::class, 'index']);
        Route::get('employee/{employment}', [EmployementApiController::class, 'show']);
        Route::post('employee', [EmployementApiController::class, 'store']);
        Route::put('employee/{employment}', [EmployementApiController::class, 'update']);
        Route::delete('employee/{employment}', [EmployementApiController::class, 'destroy']);


        //Customer
        Route::get('customer', [CustomerApiController::class, 'index']);
        Route::post('customer', [CustomerApiController::class, 'store']);
    });

    Route::prefix('technician')->middleware('auth:sanctum')->group(function () {
        Route::get('workshops/{workshop}', [WorkshopTechnicianController::class, 'show']);
    });
});



