<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\{
    Dashboard,
    Users\Index as UsersIndex,
    Promotions\Index as PromotionsIndex,
    Workshops\Index as WorkshopsIndex,
    DataCenter\Index as DataCenterIndex,
    Reports\Index as ReportsIndex,
    Settings\Index as SettingsIndex
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them
| will be assigned to the "web" middleware group.
|
*/

Route::get('/json', function () {
    return response()->json([
        'message' => 'BbiHub API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});
