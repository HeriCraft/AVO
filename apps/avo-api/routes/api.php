<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Users\Http\Controllers\AuthController;
use App\Jobs\Http\Controllers\JobController;
use App\Users\Http\Controllers\AdminDashboardController;
use App\Users\Http\Controllers\AdminUserController;
use App\Users\Http\Controllers\AdminLogsController;
use App\Settings\Http\Controllers\PlatformSettingsController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="AVO API Documentation",
 *      description="L5 Swagger OpenApi description",
 *      @OA\Contact(
 *          email="admin@avo.local"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="API Server"
 * )
 */

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/jobs', [JobController::class, 'index']);
    Route::post('/jobs', [JobController::class, 'store']);

    // Super Admin Routes
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard/metrics', [AdminDashboardController::class, 'metrics']);
        
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::patch('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
        
        Route::get('/logs', [AdminLogsController::class, 'index']);
        
        Route::get('/settings', [PlatformSettingsController::class, 'index']);
        Route::post('/settings', [PlatformSettingsController::class, 'store']);
        Route::put('/settings/{key}', [PlatformSettingsController::class, 'update']);
        Route::delete('/settings/{key}', [PlatformSettingsController::class, 'destroy']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
