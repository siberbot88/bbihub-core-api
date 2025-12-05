<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\Owner\EmployementApiController;
use App\Http\Controllers\Api\Owner\WorkshopApiController;
use App\Http\Controllers\Api\Owner\WorkshopDocumentApiController;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionItemController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VoucherApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;


Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.register');
    Route::post('login',    [AuthController::class, 'login'])->name('api.login');
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('auth/user', [AuthController::class, 'me'])->name('api.user');
    Route::post('auth/change-password', [AuthController::class, 'changePassword'])->name('api.change-password');
    Route::get('/debug/token', function (Request $request) {
        $raw = $request->bearerToken();
        $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($raw);
        if (! $pat) {
            return response()->json(['ok' => false, 'why' => 'token not found'], 401);
        }
        return [
            'ok'            => true,
            'tokenable_type'=> $pat->tokenable_type,
            'tokenable_id'  => $pat->tokenable_id,
        ];
    });


    Route::prefix('owners')->middleware('role:owner,sanctum')->name('api.owner.')->group(function () {
        // Workshops
        Route::post('workshops',[WorkshopApiController::class, 'store'])->name('workshops.store');
//        Route::put ('workshops/{workshop}',[WorkshopApiController::class, 'update'])->name('workshops.update');

        // Documents
        Route::post('documents',[WorkshopDocumentApiController::class, 'store'])->name('documents.store');
//        Route::get ('documents',[WorkshopDocumentApiController::class, 'index'])->name('documents.index');

        // Employees
        Route::get   ('employee',[EmployementApiController::class, 'index'])->name('employee.index');
        Route::post  ('employee',[EmployementApiController::class, 'store'])->name('employee.store');
        Route::get   ('employee/{employee}',[EmployementApiController::class, 'show'])->name('employee.show');
        Route::put   ('employee/{employee}',[EmployementApiController::class, 'update'])->name('employee.update');
        Route::delete('employee/{employee}',[EmployementApiController::class, 'destroy'])->name('employee.destroy');
        Route::patch ('employee/{employee}/status',[EmployementApiController::class, 'updateStatus'])->name('employee.updateStatus');

        // Customers (optional)
        Route::apiResource('customers', CustomerApiController::class);

        // Voucher
        Route::get('/vouchers', [VoucherApiController::class, 'index']);
        Route::post('/vouchers', [VoucherApiController::class, 'store']);
        Route::get('/vouchers/{voucher}', [VoucherApiController::class, 'show']);
        Route::put('/vouchers/{voucher}', [VoucherApiController::class, 'update']);
        Route::patch('/vouchers/{voucher}', [VoucherApiController::class, 'update']);
        Route::delete('/vouchers/{voucher}', [VoucherApiController::class, 'destroy']);


        // List Service
        Route::get('services',           [ServiceApiController::class, 'index']);
        Route::get('services/{service}', [ServiceApiController::class, 'show']);


        // Kendaraan
        Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
        Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    });


    Route::prefix('admins')->middleware('role:admin,sanctum')->name('api.admin.')->group(function () {
        Route::apiResource('vouchers', VoucherApiController::class);

        // Service
        Route::get   ('services',           [ServiceApiController::class, 'index']);
        Route::get   ('services/{service}', [ServiceApiController::class, 'show']);
        Route::post  ('services',           [ServiceApiController::class, 'store']);
        Route::put   ('services/{service}', [ServiceApiController::class, 'update']);
        Route::patch ('services/{service}', [ServiceApiController::class, 'update']);
        Route::delete('services/{service}', [ServiceApiController::class, 'destroy']);

        // Kendaraan
        Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
        Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    });

    Route::prefix('mechanics')->middleware('role:mechanic,sanctum')->name('api.mechanic.')->group(function () {
        //
    });

    Route::prefix('admins')->middleware('role:admin')->name('api.admin.')->group(function () {
        // ===== SERVICES (CRUD) =====
        Route::get   ('services',           [ServiceApiController::class, 'index']);
        Route::post  ('services',           [ServiceApiController::class, 'store']);
        Route::get   ('services/{service}', [ServiceApiController::class, 'show']);
        Route::put   ('services/{service}', [ServiceApiController::class, 'update']);
        Route::patch ('services/{service}', [ServiceApiController::class, 'update']);
        Route::delete('services/{service}', [ServiceApiController::class, 'destroy']);

        // ===== ADMIN FLOW =====
        Route::post('services/{service}/accept',          [AdminController::class, 'accept']);
        Route::post('services/{service}/decline',         [AdminController::class, 'decline']);
        Route::post('services/{service}/assign-mechanic', [AdminController::class, 'assignMechanic']);

        // ===== TRANSACTIONS =====
        Route::post('transactions', [TransactionController::class, 'store']);
        Route::get ('transactions/{transaction}', [TransactionController::class, 'show']);
        Route::patch ('transactions/{transaction}/items', [TransactionController::class, 'store']);
        Route::put ('transactions/{transaction}', [TransactionController::class, 'update']);
        Route::put ('transactions/{transaction}/status', [TransactionController::class, 'updateStatus']);
        Route::post('transactions/{transaction}/finalize', [TransactionController::class, 'finalize']);

        // ===== TRANSACTION ITEMS =====

        Route::post  ('transaction-items', [TransactionItemController::class, 'store']);
        Route::patch  ('transaction-items/{item}', [TransactionItemController::class, 'store']);
        Route::get  ('transaction-items/{item}', [TransactionItemController::class, 'show']);
        Route::put ('transactions/{transaction}/items/{item}', [TransactionItemController::class, 'update']);
        Route::delete('transactions/{transaction}/items/{item}', [TransactionItemController::class, 'destroy']);
    });
});

Route::prefix('admin')->group(function () {
    Route::get ('services',               [ServiceApiController::class, 'index']);
    Route::post('services',               [ServiceApiController::class, 'store']);
    Route::get ('services/{service}',     [ServiceApiController::class, 'show']);



});
