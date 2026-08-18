<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
