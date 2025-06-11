<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobilController;
use App\Http\Controllers\Api\DealerController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    Route::apiResource('mobil', MobilController::class);
    Route::apiResource('dealer', DealerController::class);
    Route::apiResource('booking', BookingController::class);
});

Route::get('/', function () {
    return response()->json(['message' => 'API Connected Successfully!'], 200);
});