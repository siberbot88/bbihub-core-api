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
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionItemController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VoucherApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;


// Midtrans Webhook (no auth required)
Route::post('v1/webhooks/midtrans', [MidtransWebhookController::class, 'handle'])
    ->name('webhook.midtrans')
    ->middleware('midtrans.whitelist');

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.login');

    // ✅ SECURITY FIX: Rate limiting for password reset endpoints
    Route::post('forgot-password', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'sendOtp'])
        ->middleware('throttle:3,10'); // Max 3 requests per 10 minutes

    Route::post('verify-otp', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'verifyOtp'])
        ->middleware('throttle:5,1'); // Max 5 attempts per minute

    Route::post('reset-password', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'resetPassword'])
        ->middleware('throttle:5,10'); // Max 5 attempts per 10 minutes
});

// Test endpoint for chat (no auth)
Route::get('v1/chat/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Chat API is working!',
        'timestamp' => now()->toIso8601String(),
    ]);
});


// Webhooks (Public)
Route::post('webhooks/midtrans', [\App\Http\Controllers\Api\PaymentCallbackController::class, 'handle']);

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('auth/user', [AuthController::class, 'me'])->name('api.user');
    Route::post('auth/change-password', [AuthController::class, 'changePassword'])->name('api.change-password');
    Route::get('/debug/token', function (Request $request) {
        $raw = $request->bearerToken();
        $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($raw);
        if (!$pat) {
            return response()->json(['ok' => false, 'why' => 'token not found'], 401);
        }
        return [
            'ok' => true,
            'tokenable_type' => $pat->tokenable_type,
            'tokenable_id' => $pat->tokenable_id,
        ];
    });

    // Chat API Routes
    Route::prefix('chat')->group(function () {
        Route::post('/send', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage'])->name('chat.send');
        Route::get('/messages', [\App\Http\Controllers\Api\ChatController::class, 'getMessages'])->name('chat.messages');
        Route::get('/history', [\App\Http\Controllers\Api\ChatController::class, 'getHistory'])->name('chat.history');
        Route::delete('/history', [\App\Http\Controllers\Api\ChatController::class, 'clearHistory'])->name('chat.clear');
        Route::post('/mark-read', [\App\Http\Controllers\Api\ChatController::class, 'markAsRead'])->name('chat.mark-read');
        Route::get('/rooms', [\App\Http\Controllers\Api\ChatController::class, 'getRooms'])->name('chat.rooms'); // Admin only
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
        Route::post('/mark-read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);
    });


    Route::prefix('owners')->middleware(['role:owner,sanctum'])->name('api.owner.')->group(function () {
        // Workshops
        Route::post('workshops', [WorkshopApiController::class, 'store'])->name('workshops.store');
        Route::put('workshops/{workshop}', [WorkshopApiController::class, 'update'])->name('workshops.update');

        // Documents
        Route::post('documents', [WorkshopDocumentApiController::class, 'store'])->name('documents.store');
        //        Route::get ('documents',[WorkshopDocumentApiController::class, 'index'])->name('documents.index');

        // Employees
        Route::get('employee', [EmployementApiController::class, 'index'])->name('employee.index');
        Route::post('employee', [EmployementApiController::class, 'store'])->name('employee.store');
        Route::get('employee/{employee}', [EmployementApiController::class, 'show'])->name('employee.show');
        Route::put('employee/{employee}', [EmployementApiController::class, 'update'])->name('employee.update');
        Route::delete('employee/{employee}', [EmployementApiController::class, 'destroy'])->name('employee.destroy');
        Route::patch('employee/{employee}/status', [EmployementApiController::class, 'updateStatus'])->name('employee.updateStatus');

        // Staff Performance (PREMIUM ONLY)
        Route::get('staff/performance', [\App\Http\Controllers\Api\Owner\StaffPerformanceController::class, 'index'])
            ->middleware('premium')
            ->name('staff.performance.index');
        Route::get('staff/{user_id}/performance', [\App\Http\Controllers\Api\Owner\StaffPerformanceController::class, 'show'])
            ->middleware('premium')
            ->name('staff.performance.show');

        // Analytics Report (PREMIUM ONLY)
        Route::get('analytics/report', [\App\Http\Controllers\Api\Owner\ReportAnalyticsController::class, 'getReport'])
            ->middleware('premium')
            ->name('analytics.report');

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
        Route::get('services', [ServiceApiController::class, 'index']);
        Route::get('services/{service}', [ServiceApiController::class, 'show']);
        Route::put('services/{service}', [ServiceApiController::class, 'update']);
        Route::patch('services/{service}', [ServiceApiController::class, 'update']);


        // Kendaraan
        Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
        Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    });

    // ADMIN ROUTES (Consolidated)
    Route::prefix('admins')->middleware('role:admin,sanctum')->name('api.admin.')->group(function () {
        Route::apiResource('vouchers', VoucherApiController::class);
        //employee
        Route::get('users', [AdminController::class, 'employees']);
        Route::get('users/{user}', [AdminController::class, 'employee']);
        Route::get('users/{user}', [AdminController::class, 'employee']);
        Route::put('users/{user}', [AdminController::class, 'updateEmployee']);

        // Dashboard
        Route::get('dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'getStats']);

        // Service
        Route::get   ('services',           [ServiceApiController::class, 'index']);
        Route::get   ('services/{service}', [ServiceApiController::class, 'show']);
        Route::post  ('services',           [ServiceApiController::class, 'store']);
        Route::post  ('services/walk-in',   [ServiceApiController::class, 'storeWalkIn']);
        Route::put   ('services/{service}', [ServiceApiController::class, 'update']);
        Route::patch ('services/{service}', [ServiceApiController::class, 'update']);
        Route::delete('services/{service}', [ServiceApiController::class, 'destroy']);

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

        // ===== ADMIN FLOW =====
        Route::post('services/{service}/accept',          [AdminController::class, 'accept']);
        Route::post('services/{service}/decline',         [AdminController::class, 'decline']);
        Route::post('services/{service}/assign-mechanic', [AdminController::class, 'assignMechanic']);

        // ===== VEHICLES =====
        Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
        Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    });

    Route::prefix('mechanics')->middleware('role:mechanic,sanctum')->name('api.mechanic.')->group(function () {
        //
    });

    // Owner SaaS Subscription
    Route::prefix('owner/subscription')->group(function () {
        Route::post('checkout', [\App\Http\Controllers\Api\OwnerSubscriptionController::class, 'checkout'])->name('owner.subscription.checkout');
        Route::post('start-trial', [\App\Http\Controllers\Api\OwnerSubscriptionController::class, 'startTrial'])->name('owner.subscription.start-trial');
        Route::post('cancel', [\App\Http\Controllers\Api\OwnerSubscriptionController::class, 'cancel'])->name('owner.subscription.cancel');
        Route::post('check-status', [\App\Http\Controllers\Api\OwnerSubscriptionController::class, 'checkStatus'])->name('owner.subscription.check-status');
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

    /** Admin Routes - Audit Logs */
    Route::prefix('admin')->middleware('role:owner|admin,sanctum')->group(function () {
        Route::get('audit-logs', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'index']);
        Route::get('audit-logs/events', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'events']);
        Route::get('audit-logs/{auditLog}', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'show']);
    });
});

Route::prefix('admin')->group(function () {
    Route::get ('services',               [ServiceApiController::class, 'index']);
    Route::post('services',               [ServiceApiController::class, 'store']);
    Route::get ('services/{service}',     [ServiceApiController::class, 'show']);

});
