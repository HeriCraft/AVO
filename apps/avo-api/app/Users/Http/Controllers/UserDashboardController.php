<?php

namespace App\Users\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Persistence\Models\Candidate;
use App\Persistence\Models\Application;
use App\Persistence\Models\JobPost;
use App\Persistence\Models\Interview;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class UserDashboardController extends Controller
{
    #[OA\Get(
        path: "/api/user/dashboard/metrics",
        summary: "Get user dashboard metrics",
        security: [["bearerAuth" => []]],
        tags: ["Dashboard"],
        responses: [
            new OA\Response(response: 200, description: "Dashboard metrics payload")
        ]
    )]
    public function metrics()
    {
        $userId = Auth::id();

        // Subquery for candidates belonging to the user's jobs
        $candidatesQuery = Application::whereHas('jobPost', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });

        // 1. KPIs
        $pendingActionCount = (clone $candidatesQuery)
            ->where('status', 'PENDING_HUMAN_REVIEW')
            ->count();

        $activeJobsCount = JobPost::where('user_id', $userId)
            ->whereIn('status', ['PUBLISHED', 'ACTIVE'])
            ->count();

        $aiInterviews30d = Interview::whereHas('candidate.applications.jobPost', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('scheduled_at', '>=', Carbon::now()->subDays(30))->count();

        $totalCandidates = (clone $candidatesQuery)->count();
        $hiredCandidates = (clone $candidatesQuery)->where('status', 'HIRED')->count();
        $conversionRate = $totalCandidates > 0 ? round(($hiredCandidates / $totalCandidates) * 100, 1) : 0;

        // 2. Charts Data
        // AI Score Distribution
        $aiScoresData = (clone $candidatesQuery)
            ->select('ai_score', DB::raw('count(*) as count'))
            ->whereNotNull('ai_score')
            ->groupBy('ai_score')
            ->get()
            ->pluck('count', 'ai_score');

        // Funnel Distribution
        $funnelData = (clone $candidatesQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Acquisition
        $fourteenDaysAgo = Carbon::now()->subDays(14)->startOfDay();
        $acquisitionRaw = (clone $candidatesQuery)
            ->where('created_at', '>=', $fourteenDaysAgo)
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });
            
        $acquisitionData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $acquisitionData[$date] = isset($acquisitionRaw[$date]) ? $acquisitionRaw[$date]->count() : 0;
        }

        // 3. Widgets
        $topGreenCandidates = (clone $candidatesQuery)
            ->with(['jobPost:id,title', 'candidate:id,firstname,lastname'])
            ->where('ai_score', 'GREEN')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($app) {
                $app->name = trim($app->candidate?->firstname . ' ' . $app->candidate?->lastname);
                return $app;
            });

        $todaysInterviews = Interview::with(['candidate.applications.jobPost:id,title'])
            ->whereHas('candidate.applications.jobPost', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->whereDate('scheduled_at', Carbon::today())
            ->orderBy('scheduled_at', 'asc')
            ->get()
            ->map(function($int) {
                $int->candidate->name = trim($int->candidate?->firstname . ' ' . $int->candidate?->lastname);
                return $int;
            });

        return response()->json([
            'kpis' => [
                'pending_action_count' => $pendingActionCount,
                'ai_interviews_30d' => $aiInterviews30d,
                'active_jobs_count' => $activeJobsCount,
                'conversion_rate' => $conversionRate,
            ],
            'charts' => [
                'ai_scores' => [
                    'GREEN' => $aiScoresData['GREEN'] ?? 0,
                    'YELLOW' => $aiScoresData['YELLOW'] ?? 0,
                    'RED' => $aiScoresData['RED'] ?? 0,
                ],
                'funnel' => [
                    'NEW' => $funnelData['NEW'] ?? 0,
                    'PENDING_HUMAN_REVIEW' => $funnelData['PENDING_HUMAN_REVIEW'] ?? 0,
                    'SHORTLISTED' => $funnelData['SHORTLISTED'] ?? 0,
                    'HIRED' => $funnelData['HIRED'] ?? 0,
                ],
                'acquisition' => $acquisitionData,
            ],
            'widgets' => [
                'top_green_candidates' => $topGreenCandidates,
                'todays_interviews' => $todaysInterviews,
            ]
        ]);
    }

    #[OA\Get(
        path: "/api/user/dashboard/details",
        summary: "Get contextual details for dashboard KPI",
        security: [["bearerAuth" => []]],
        tags: ["Dashboard"],
        parameters: [
            new OA\Parameter(name: "filter", in: "query", required: true, schema: new OA\Schema(type: "string", enum: ["pending_actions", "active_jobs", "ai_interviews"]))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detailed data for KPI")
        ]
    )]
    public function details(Request $request)
    {
        $filter = $request->query('filter', 'pending_actions');
        $userId = Auth::id();

        if ($filter === 'pending_actions') {
            $data = Application::with(['jobPost:id,title', 'candidate:id,firstname,lastname'])
                ->whereHas('jobPost', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->where('status', 'PENDING_HUMAN_REVIEW')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($app) {
                    $app->name = trim($app->candidate?->firstname . ' ' . $app->candidate?->lastname);
                    return $app;
                });
            return response()->json(['type' => 'candidates', 'data' => $data]);
        }

        if ($filter === 'active_jobs') {
            $data = JobPost::withCount('applications as candidates_count')
                ->where('user_id', $userId)
                ->whereIn('status', ['PUBLISHED', 'ACTIVE'])
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json(['type' => 'jobs', 'data' => $data]);
        }

        if ($filter === 'ai_interviews') {
            $data = Interview::with(['candidate.applications.jobPost:id,title'])
                ->whereHas('candidate.applications.jobPost', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->where('scheduled_at', '>=', Carbon::now()->subDays(30))
                ->orderBy('scheduled_at', 'desc')
                ->get()
                ->map(function($int) {
                    $int->candidate->name = trim($int->candidate?->firstname . ' ' . $int->candidate?->lastname);
                    return $int;
                });
            return response()->json(['type' => 'interviews', 'data' => $data]);
        }

        return response()->json(['type' => 'none', 'data' => []]);
    }
}
