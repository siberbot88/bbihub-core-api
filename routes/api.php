<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\Owner\EmployementApiController;
use App\Http\Controllers\Api\Owner\WorkshopApiController;
use App\Http\Controllers\Api\Owner\WorkshopDocumentApiController;
use App\Http\Controllers\Api\ServiceApiContoller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceController;

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.register');
    Route::post('login',    [AuthController::class, 'login'])->name('api.login');
});

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
        Route::get ('documents',[WorkshopDocumentApiController::class, 'index'])->name('documents.index');

        // Employees
        Route::get   ('employee',[EmployementApiController::class, 'index'])->name('employee.index');
        Route::post  ('employee',[EmployementApiController::class, 'store'])->name('employee.store');
        Route::get   ('employee/{employee}',[EmployementApiController::class, 'show'])->name('employee.show');
        Route::put   ('employee/{employee}',[EmployementApiController::class, 'update'])->name('employee.update');
        Route::delete('employee/{employee}',[EmployementApiController::class, 'destroy'])->name('employee.destroy');
        Route::patch ('employee/{employee}/status',[EmployementApiController::class, 'updateStatus'])->name('employee.updateStatus');

        // Customers (optional)
        Route::apiResource('customers', CustomerApiController::class);
    });

    Route::prefix('mechanics')->middleware('role:mechanic,sanctum')->name('api.mechanic.')->group(function () {
        //
    });

    Route::prefix('admins')->middleware('role:admin,sanctum')->name('api.admin.')->group(function () {
        //
        Route::apiResource('services', \App\Http\Controllers\Api\ServiceController::class);
        Route::get('/admin/services',            [ServiceController::class, 'index']);   // ?status=&date=&q=&per_page=
        Route::get('/admin/services/{service}',  [ServiceController::class, 'show']);
        Route::post('/admin/services',  [ServiceController::class, 'store']);

        // AKSI ALUR BARU
        Route::post('/admin/services/{service}/accept', [ServiceController::class, 'accept']);   // requested -> pending
        Route::post('/admin/services/{service}/reject', [ServiceController::class, 'reject']);   // requested -> rejected
        Route::post('/admin/services/{service}/assign-mechanic', [ServiceController::class, 'assignMechanic']); // pending -> (tetap pending)
        Route::post('/admin/services/{service}/start',  [ServiceController::class, 'startWork']);    // pending -> in progress
        Route::post('/admin/services/{service}/finish', [ServiceController::class, 'finishWork']);    // in progress -> completed
        Route::post('/admin/services/{service}/pay',    [ServiceController::class, 'confirmPayment']); // completed -> paid

    });
});

Route::prefix('admin')->group(function () {
    Route::get ('services',               [ServiceApiContoller::class, 'index']);
    Route::post('services',               [ServiceApiContoller::class, 'store']);
    Route::get ('services/{service}',     [ServiceApiContoller::class, 'show']);
});
