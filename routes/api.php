<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\CustomerMembershipController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\Owner\EmployementApiController;
use App\Http\Controllers\Api\Owner\WorkshopApiController;
use App\Http\Controllers\Api\Owner\WorkshopDocumentApiController;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VoucherApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Midtrans Webhook (no auth required)
Route::post('v1/webhooks/midtrans', [MidtransWebhookController::class, 'handle'])->name('webhook.midtrans');

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
        Route::put ('workshops/{workshop}',[WorkshopApiController::class, 'update'])->name('workshops.update');

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

        // Staff Performance
        Route::get('staff/performance', [\App\Http\Controllers\Api\Owner\StaffPerformanceController::class, 'index'])->name('staff.performance.index');
        Route::get('staff/{user_id}/performance', [\App\Http\Controllers\Api\Owner\StaffPerformanceController::class, 'show'])->name('staff.performance.show');


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

    // Owner SaaS Subscription
    Route::prefix('owner/subscription')->group(function () {
        Route::post('checkout', [\App\Http\Controllers\Api\OwnerSubscriptionController::class, 'checkout'])->name('owner.subscription.checkout');
    });

    // Membership Routes (for customers)
    Route::prefix('memberships')->group(function () {
        // Get available memberships for a workshop
        Route::get('workshops/{workshop}', [MembershipController::class, 'index'])->name('memberships.index');
        Route::get('{membership}', [MembershipController::class, 'show'])->name('memberships.show');
        
        // Customer membership management
        Route::get('customer/active', [CustomerMembershipController::class, 'show'])->name('customer.membership.show');
        Route::post('customer/purchase', [CustomerMembershipController::class, 'purchase'])->name('customer.membership.purchase');
        Route::post('customer/cancel', [CustomerMembershipController::class, 'cancel'])->name('customer.membership.cancel');
        Route::put('customer/auto-renew', [CustomerMembershipController::class, 'updateAutoRenew'])->name('customer.membership.auto-renew');
        Route::get('customer/payment-status/{orderId}', [CustomerMembershipController::class, 'checkPaymentStatus'])->name('customer.membership.payment-status');
    });
});

Route::prefix('admin')->group(function () {
    Route::get ('services',               [ServiceApiController::class, 'index']);
    Route::post('services',               [ServiceApiController::class, 'store']);
    Route::get ('services/{service}',     [ServiceApiController::class, 'show']);



});
