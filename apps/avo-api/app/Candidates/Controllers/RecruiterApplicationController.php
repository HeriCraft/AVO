<?php

namespace App\Candidates\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Persistence\Models\Application;

class RecruiterApplicationController extends Controller
{
    public function index(Request $request)
    {
        $applications = Application::with(['candidate:id,firstname,lastname,email', 'jobPost:id,title'])
            ->whereHas('jobPost', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($applications, 200);
    }
}
