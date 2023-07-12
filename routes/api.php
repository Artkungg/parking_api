<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ParkingController;
use App\Http\Controllers\Api\ReserveController;
use App\Http\Controllers\Api\SlotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:api')->group(function(){
    Route::apiResource('parking', ParkingController::class);
    Route::apiResource('slot', SlotController::class);
    Route::apiResource('reserve', ReserveController::class);
    Route::get('report/{id}/{time}', [ParkingController::class, 'report']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::get('nearbyParking', [ParkingController::class, 'nearbyParking']);
Route::post('parking_check_in/{id}', [ReserveController::class, 'check_in']);
Route::put('parking_check_out/{id}', [ReserveController::class, 'check_out']);