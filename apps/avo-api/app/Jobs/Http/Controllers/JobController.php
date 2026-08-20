<?php

namespace App\Jobs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Persistence\Models\JobPost;
use App\Jobs\Events\JobPublished;
use App\Jobs\Events\JobPostCreated;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:DRAFT,PUBLISHED,CLOSED',
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('jobs', 'public');
        }

        $job = JobPost::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'user_id' => Auth::id(),
            'cover_image_path' => $coverImagePath,
        ]);

        if ($job->status === 'PUBLISHED') {
            JobPublished::dispatch($job);
        }

        JobPostCreated::dispatch($job->id, $job->description);

        return response()->json($job, 201);
    }

    public function update(Request $request, $id)
    {
        $job = JobPost::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:DRAFT,PUBLISHED,CLOSED',
            'cover_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($job->cover_image_path) {
                Storage::disk('public')->delete($job->cover_image_path);
            }
            $validated['cover_image_path'] = $request->file('cover_image')->store('jobs', 'public');
        }
        unset($validated['cover_image']);

        $wasPublished = $job->status !== 'PUBLISHED' && ($validated['status'] ?? '') === 'PUBLISHED';
        $descriptionChanged = isset($validated['description']) && $job->description !== $validated['description'];

        $job->update($validated);

        if ($wasPublished) {
            JobPublished::dispatch($job);
        }

        if ($descriptionChanged) {
            // Re-trigger tag generation if description changed
            JobPostCreated::dispatch($job->id, $job->description);
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
        
        if ($job->cover_image_path) {
            Storage::disk('public')->delete($job->cover_image_path);
        }
        
        $job->delete();

        return response()->json(null, 204);
    }
}
