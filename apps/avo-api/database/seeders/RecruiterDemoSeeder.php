<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;
use App\Persistence\Models\Candidate;
use App\Persistence\Models\Interview;
use Carbon\Carbon;

class RecruiterDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'USER')->first();

        if (!$user) {
            $user = User::create([
                'username' => 'recruiter_demo',
                'name' => 'Demo Recruiter',
                'email' => 'recruiter@avo.local',
                'password' => bcrypt('password'),
                'role' => 'USER',
            ]);
        }

        // Create 3 active jobs
        $jobs = [];
        $jobTitles = ['Senior Frontend Engineer', 'Backend Developer (Go)', 'Product Designer'];
        foreach ($jobTitles as $title) {
            $jobs[] = JobPost::create([
                'user_id' => $user->id,
                'title' => $title,
                'description' => "We are looking for a $title...",
                'status' => 'PUBLISHED', // Maps to ACTIVE
            ]);
        }

        // Create 50 candidates spread over the last 14 days
        $candidates = [];
        $statuses = ['NEW', 'PENDING_HUMAN_REVIEW', 'SHORTLISTED', 'HIRED', 'REJECTED'];
        $scores = ['GREEN', 'YELLOW', 'RED'];

        for ($i = 0; $i < 50; $i++) {
            $job = $jobs[array_rand($jobs)];
            
            // Bias statuses a bit
            $status = $statuses[array_rand($statuses)];
            $score = $scores[array_rand($scores)];

            // Random date in the last 14 days
            $daysAgo = rand(0, 14);
            $createdAt = Carbon::now()->subDays($daysAgo);

            $candidates[] = Candidate::create([
                'job_post_id' => $job->id,
                'name' => 'Candidate ' . ($i + 1),
                'status' => $status,
                'ai_score' => $score,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // Create 3 interviews for today
        $todayCandidates = collect($candidates)->whereIn('status', ['SHORTLISTED', 'PENDING_HUMAN_REVIEW'])->take(3);
        
        foreach ($todayCandidates as $candidate) {
            Interview::create([
                'candidate_id' => $candidate->id,
                'scheduled_at' => Carbon::now()->addHours(rand(1, 5)), // Today, later
                'status' => 'SCHEDULED',
            ]);
        }
    }
}
