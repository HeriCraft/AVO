<?php

namespace App\Users\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Persistence\Models\ActivityLog;
use OpenApi\Attributes as OA;

class AdminLogsController extends Controller
{
    #[OA\Get(
        path: "/api/admin/logs",
        summary: "List activity logs",
        security: [["bearerAuth" => []]],
        tags: ["Admin Logs"],
        responses: [new OA\Response(response: 200, description: "Logs list")]
    )]
    public function index()
    {
        return response()->json(ActivityLog::with('user:id,name,email')->latest()->paginate(50));
    }
}
