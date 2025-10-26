<?php

use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Owner\EmployementApiController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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



