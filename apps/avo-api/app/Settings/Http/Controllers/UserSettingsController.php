<?php

namespace App\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Persistence\Models\UserSetting;
use OpenApi\Attributes as OA;

class UserSettingsController extends Controller
{
    #[OA\Get(
        path: "/api/user/settings",
        summary: "Get current user settings",
        security: [["bearerAuth" => []]],
        tags: ["User Settings"],
        responses: [
            new OA\Response(response: 200, description: "User settings")
        ]
    )]
    public function show()
    {
        $settings = UserSetting::firstOrCreate(['user_id' => Auth::id()]);
        return response()->json($settings);
    }

    #[OA\Put(
        path: "/api/user/settings",
        summary: "Update user settings and profile",
        security: [["bearerAuth" => []]],
        tags: ["User Settings"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "first_name", type: "string", nullable: true),
                    new OA\Property(property: "last_name", type: "string", nullable: true),
                    new OA\Property(property: "company", type: "string", nullable: true),
                    new OA\Property(property: "company_role", type: "string", nullable: true),
                    new OA\Property(property: "ai_voice_tone", type: "string", nullable: true),
                    new OA\Property(property: "ai_interview_language", type: "string", nullable: true),
                    new OA\Property(property: "ai_strictness_level", type: "string", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Settings updated")
        ]
    )]
    public function update(Request $request)
    {
        $user = Auth::user();

        $validatedUser = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'company_role' => 'nullable|string|max:255',
        ]);
        
        $validatedUser['name'] = trim(($validatedUser['first_name'] ?? '') . ' ' . ($validatedUser['last_name'] ?? '')) ?: $user->username;
        $user->update($validatedUser);

        $validatedSettings = $request->validate([
            'ai_voice_tone' => 'nullable|string|max:255',
            'ai_interview_language' => 'nullable|string|max:255',
            'ai_strictness_level' => 'nullable|string|max:255',
        ]);

        $settings = UserSetting::firstOrCreate(['user_id' => $user->id]);
        $settings->update($validatedSettings);

        return response()->json([
            'user' => $user,
            'settings' => $settings
        ]);
    }
}
