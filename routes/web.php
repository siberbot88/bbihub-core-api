<?php

use App\Http\Controllers\WorkshopController;
use App\Livewire\Counter;
use App\Livewire\Login;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('workshop')->group(function () {
    Route::get('/', [WorkshopController::class, 'index'])->name('workshop.index');
    Route::get('/create', [WorkshopController::class, 'create'])->name('workshop.create');
    Route::post('/store', [WorkshopController::class, 'store'])->name('workshop.store');
    Route::get('/{workshop}/edit', [WorkshopController::class, 'edit'])->name('workshop.edit');
    Route::patch('/{workshop}', [WorkshopController::class, 'update'])->name('workshop.update');
})->withoutMiddleware(VerifyCsrfToken::class);
