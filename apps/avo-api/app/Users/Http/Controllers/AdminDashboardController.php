<?php

namespace App\Users\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Persistence\Models\User;
use App\Persistence\Models\ActivityLog;
use OpenApi\Attributes as OA;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    #[OA\Get(
        path: "/api/admin/dashboard/metrics",
        summary: "Get Admin Dashboard Metrics",
        security: [["bearerAuth" => []]],
        tags: ["Admin"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dashboard Metrics"
            )
        ]
    )]
    public function metrics()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'ACTIVE')->count();
        
        $roleDistribution = User::select('role')
            ->selectRaw('count(*) as count')
            ->groupBy('role')
            ->get();

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $registrations = User::where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $recentLogs = ActivityLog::with('user:id,name')
            ->latest()
            ->take(6)
            ->get();

        return response()->json([
            'kpis' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
            ],
            'role_distribution' => $roleDistribution,
            'registrations_30_days' => $registrations,
            'recent_logs' => $recentLogs,
        ]);
    }
}
