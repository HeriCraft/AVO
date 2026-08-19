<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Users\Http\Controllers\AuthController;
use App\Jobs\Http\Controllers\JobController;

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
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
