<?php

namespace App\Candidates\Controllers;

use App\Persistence\Models\JobPost;
use Illuminate\Routing\Controller;

class PublicJobController extends Controller
{
    /**
     * Fetch a specific published job for the public job board.
     */
    public function show($id)
    {
        $job = JobPost::select('id', 'title', 'description', 'tags', 'cover_image_path', 'created_at', 'status')
            ->where('id', $id)
            ->where('status', 'PUBLISHED')
            ->firstOrFail();

        return response()->json($job);
    }
}
