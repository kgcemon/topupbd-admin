<?php

use App\Http\Controllers\api\admin\AdminAuthController;
use App\Http\Controllers\api\admin\AdminOrderController;
use App\Http\Controllers\api\admin\DashboardController;
use App\Http\Controllers\api\auth\AuthController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\WalletController;
use Illuminate\Support\Facades\Route;

//public route
Route::group(['middleware' => 'throttle:15,1'], function () {
    Route::post('/login', [AuthController::class, 'Login']);
    Route::post('/login-with-google', [AuthController::class, 'loginWithGoogleToken']);
    Route::get('/my-orders/{user_id}', [OrderController::class, 'GetMyOrders']);
});


//all prodected Routes
Route::group(['middleware' => ['auth:sanctum', 'throttle:15,1']], function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/order-with-wallet', [OrderController::class, 'AddProductOrder']);
    Route::get('/deposit-wallet', [WalletController::class, 'Deposit']);
    Route::get('/deposit-history', [WalletController::class, 'WalletHistory']);
});


//Admin login Api Routes
Route::post('/admin-login', [AdminAuthController::class, 'adminLogin'])->middleware('throttle:15,1');

Route::middleware(['auth:sanctum', 'throttle:15,1'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::post('/orders', [AdminOrderController::class, 'updateOrder']);
});
