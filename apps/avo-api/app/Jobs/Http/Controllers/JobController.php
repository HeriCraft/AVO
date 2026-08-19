<?php

namespace App\Jobs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Persistence\Models\JobPost;
use App\Jobs\Events\JobPublished;
use OpenApi\Attributes as OA;

class JobController extends Controller
{
    #[OA\Get(
        path: "/api/jobs",
        summary: "List all jobs for current user",
        security: [["bearerAuth" => []]],
        tags: ["Jobs"],
        responses: [
            new OA\Response(response: 200, description: "List of jobs")
        ]
    )]
    public function index()
    {
        return response()->json(JobPost::with('user:id,name')->where('user_id', Auth::id())->latest()->get());
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
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "status", type: "string", enum: ["DRAFT", "PUBLISHED", "CLOSED"])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Job created")
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:DRAFT,PUBLISHED,CLOSED',
        ]);

        $job = JobPost::create([
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

    #[OA\Put(
        path: "/api/jobs/{id}",
        summary: "Update an existing job",
        security: [["bearerAuth" => []]],
        tags: ["Jobs"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "status", type: "string", enum: ["DRAFT", "PUBLISHED", "CLOSED"])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Job updated"),
            new OA\Response(response: 404, description: "Job not found")
        ]
    )]
    public function update(Request $request, $id)
    {
        $job = JobPost::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:DRAFT,PUBLISHED,CLOSED',
        ]);

        $wasPublished = $job->status !== 'PUBLISHED' && ($validated['status'] ?? '') === 'PUBLISHED';

        $job->update($validated);

        if ($wasPublished) {
            JobPublished::dispatch($job);
        }

        return response()->json($job);
    }

    #[OA\Delete(
        path: "/api/jobs/{id}",
        summary: "Delete a job",
        security: [["bearerAuth" => []]],
        tags: ["Jobs"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 204, description: "Job deleted"),
            new OA\Response(response: 404, description: "Job not found")
        ]
    )]
    public function destroy($id)
    {
        $job = JobPost::where('user_id', Auth::id())->findOrFail($id);
        $job->delete();

        return response()->json(null, 204);
    }
}
