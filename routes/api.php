<?php

use App\Http\Controllers\api\auth\AuthController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\WalletController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'Login'] );

Route::post('/login-with-google', [AuthController::class, 'loginWithGoogleToken']);

Route::group(['middleware' => ['auth:sanctum', 'throttle:15,1']], function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/order-with-wallet', [OrderController::class, 'AddProductOrder']);
    Route::get('/deposit-wallet', [WalletController::class, 'Deposit']);
});

