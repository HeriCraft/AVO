<?php

use Illuminate\Support\Facades\Route;
use App\Users\Controllers\AuthController;
use App\Users\Controllers\AdminDashboardController;
use App\Users\Middlewares\EnsureUserIsSuperAdmin;

Route::prefix('api/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('api/admin')->middleware(['auth:sanctum', EnsureUserIsSuperAdmin::class])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
});
