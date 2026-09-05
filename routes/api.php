<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
});

Route::prefix('jwt')->group(function () {
    Route::post('/login', [JwtAuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [JwtAuthController::class, 'me']);
        Route::post('/logout', [JwtAuthController::class, 'logout']);
        Route::post('/refresh', [JwtAuthController::class, 'refresh']);
        Route::get('/admin-only', [JwtAuthController::class, 'adminOnly']);
    });
});

Route::apiResource('orders', OrderController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);