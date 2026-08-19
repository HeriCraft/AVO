<?php

namespace App\Users\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Persistence\Models\User;
use App\Persistence\Models\ActivityLog;
use App\Users\Events\UserCreatedByAdmin;
use Illuminate\Support\Facades\Hash;
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

    #[OA\Post(
        path: "/api/admin/users",
        summary: "Create a new user from admin dashboard",
        security: [["bearerAuth" => []]],
        tags: ["Admin Users"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "email", "password", "role"],
                properties: [
                    new OA\Property(property: "username", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string", minLength: 8),
                    new OA\Property(property: "role", type: "string", enum: ["USER"]),
                    new OA\Property(property: "first_name", type: "string", nullable: true),
                    new OA\Property(property: "last_name", type: "string", nullable: true),
                    new OA\Property(property: "company", type: "string", nullable: true),
                    new OA\Property(property: "company_role", type: "string", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "User created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:USER',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'company' => 'nullable|string',
            'company_role' => 'nullable|string',
        ]);

        $validated['name'] = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')) ?: $validated['username'];
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'USER_CREATED',
            'description' => "Created new user: {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        UserCreatedByAdmin::dispatch($user, $request->user());

        return response()->json($user, 201);
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
