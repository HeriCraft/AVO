<?php

namespace App\Users\Controllers;

use App\Persistence\Models\ActivityLog;
use App\Persistence\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController
{
    public function index()
    {
        $totalUsers = User::count();
        $recentActivities = ActivityLog::with('user')->latest()->take(10)->get();

        return response()->json([
            'total_users' => $totalUsers,
            'recent_activities' => $recentActivities
        ]);
    }
}
