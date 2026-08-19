<?php

namespace App\Users\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Persistence\Models\User;
use OpenApi\Attributes as OA;

class AdminUserController extends Controller
{
    #[OA\Get(
        path: "/api/admin/users",
        summary: "List users",
        security: [["bearerAuth" => []]],
        tags: ["Admin Users"],
        responses: [new OA\Response(response: 200, description: "List of users")]
    )]
    public function index()
    {
        return response()->json(User::latest()->get());
    }

    #[OA\Patch(
        path: "/api/admin/users/{id}/toggle-status",
        summary: "Toggle user status",
        security: [["bearerAuth" => []]],
        tags: ["Admin Users"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [new OA\Response(response: 200, description: "Status updated")]
    )]
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';
        $user->save();

        return response()->json($user);
    }
}
