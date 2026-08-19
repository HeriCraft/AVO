<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;
use Illuminate\Http\JsonResponse;

#[OA\Info(
    version: "1.0.0",
    description: "L5 Swagger OpenApi description",
    title: "AVO API Documentation",
    contact: new OA\Contact(email: "admin@avo.local")
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class Controller
{
    #[OA\Get(
        path: "/api/health",
        summary: "Health Check",
        description: "Returns the health status of the API",
        tags: ["System"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation"
            )
        ]
    )]
    public function health(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
