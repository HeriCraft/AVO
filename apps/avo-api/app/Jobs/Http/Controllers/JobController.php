<?php

namespace App\Jobs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Persistence\Models\Job;
use App\Jobs\Events\JobPublished;
use OpenApi\Attributes as OA;

class JobController extends Controller
{
    #[OA\Get(
        path: "/api/jobs",
        summary: "List all jobs",
        security: [["bearerAuth" => []]],
        tags: ["Jobs"],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of jobs",
                content: new OA\JsonContent(type: "array", items: new OA\Items(
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "title", type: "string"),
                        new OA\Property(property: "status", type: "string"),
                        new OA\Property(property: "created_at", type: "string", format: "date-time")
                    ]
                ))
            )
        ]
    )]
    public function index()
    {
        return response()->json(Job::with('user:id,name')->latest()->get());
    }

    #[OA\Post(
        path: "/api/jobs",
        summary: "Create a new job",
        security: [["bearerAuth" => []]],
        tags: ["Jobs"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "description", "status"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Senior AI Engineer"),
                    new OA\Property(property: "description", type: "string", example: "We are looking for a..."),
                    new OA\Property(property: "status", type: "string", enum: ["DRAFT", "PUBLISHED", "CLOSED"])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Job created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "title", type: "string"),
                        new OA\Property(property: "status", type: "string")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            )
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:DRAFT,PUBLISHED,CLOSED',
        ]);

        $job = Job::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'user_id' => Auth::id(),
        ]);

        if ($job->status === 'PUBLISHED') {
            JobPublished::dispatch($job);
        }

        return response()->json($job, 201);
    }
}
