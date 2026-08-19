<?php

namespace App\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Persistence\Models\PlatformSetting;
use OpenApi\Attributes as OA;

class PlatformSettingsController extends Controller
{
    #[OA\Get(
        path: "/api/admin/settings",
        summary: "List platform settings",
        security: [["bearerAuth" => []]],
        tags: ["Admin Settings"],
        responses: [new OA\Response(response: 200, description: "Settings")]
    )]
    public function index()
    {
        return response()->json(PlatformSetting::all());
    }

    #[OA\Post(
        path: "/api/admin/settings",
        summary: "Create a platform setting",
        security: [["bearerAuth" => []]],
        tags: ["Admin Settings"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["key"],
                properties: [
                    new OA\Property(property: "key", type: "string"),
                    new OA\Property(property: "value", type: "object")
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Setting created")]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:platform_settings,key',
            'value' => 'nullable',
        ]);

        $setting = PlatformSetting::create($validated);
        return response()->json($setting, 201);
    }

    #[OA\Put(
        path: "/api/admin/settings/{key}",
        summary: "Update a platform setting",
        security: [["bearerAuth" => []]],
        tags: ["Admin Settings"],
        parameters: [
            new OA\Parameter(name: "key", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "value", type: "object")
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Setting updated")]
    )]
    public function update(Request $request, $key)
    {
        $setting = PlatformSetting::where('key', $key)->firstOrFail();
        $setting->update(['value' => $request->input('value')]);

        return response()->json($setting);
    }

    #[OA\Delete(
        path: "/api/admin/settings/{key}",
        summary: "Delete a platform setting",
        security: [["bearerAuth" => []]],
        tags: ["Admin Settings"],
        parameters: [
            new OA\Parameter(name: "key", in: "path", required: true, schema: new OA\Schema(type: "string"))
        ],
        responses: [new OA\Response(response: 204, description: "Setting deleted")]
    )]
    public function destroy($key)
    {
        PlatformSetting::where('key', $key)->delete();
        return response()->noContent();
    }
}
