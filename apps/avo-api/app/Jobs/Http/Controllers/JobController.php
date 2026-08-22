<?php

namespace App\Jobs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Persistence\Models\JobPost;
use App\Jobs\Events\JobPublishedEvent;
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

    #[OA\Get(
        path: "/api/jobs/{id}/tags",
        summary: "Get tags for a job",
        security: [["bearerAuth" => []]],
        tags: ["Jobs"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Job tags")
        ]
    )]
    public function tags($id)
    {
        $job = JobPost::where('user_id', Auth::id())->findOrFail($id);
        return response()->json(['tags' => $job->tags]);
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

        JobPublishedEvent::dispatch($job->id, $job->title, $job->description);

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

        $descriptionChanged = isset($validated['description']) && $job->description !== $validated['description'];
        $titleChanged = isset($validated['title']) && $job->title !== $validated['title'];

        $job->update($validated);

        if ($descriptionChanged || $titleChanged) {
            // Re-trigger tag generation if description or title changed
            JobPublishedEvent::dispatch($job->id, $job->title, $job->description);
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
