<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;
use App\Persistence\Models\Candidate;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_metrics_returns_successful_payload()
    {
        $user = User::factory()->create(['role' => 'USER']);
        
        $job = JobPost::create([
            'user_id' => $user->id,
            'title' => 'Test Job',
            'description' => 'Test',
            'status' => 'PUBLISHED'
        ]);

        Candidate::create([
            'job_post_id' => $job->id,
            'name' => 'John Doe',
            'status' => 'NEW',
            'ai_score' => 'GREEN'
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/user/dashboard/metrics');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'kpis' => ['pending_action_count', 'ai_interviews_30d', 'active_jobs_count', 'conversion_rate'],
                     'charts' => ['ai_scores', 'funnel', 'acquisition'],
                     'widgets' => ['top_green_candidates', 'todays_interviews']
                 ]);
                 
        $this->assertEquals(1, $response->json('kpis.active_jobs_count'));
    }

    public function test_user_dashboard_only_aggregates_own_data()
    {
        $user1 = User::factory()->create(['role' => 'USER']);
        $user2 = User::factory()->create(['role' => 'USER']);
        
        // User 2 creates a job and candidate
        $job2 = JobPost::create([
            'user_id' => $user2->id,
            'title' => 'Other Job',
            'description' => 'Test',
            'status' => 'PUBLISHED'
        ]);
        Candidate::create([
            'job_post_id' => $job2->id,
            'name' => 'Jane Doe',
            'status' => 'PENDING_HUMAN_REVIEW',
            'ai_score' => 'GREEN'
        ]);

        // User 1 requests metrics
        $response = $this->actingAs($user1, 'api')->getJson('/api/user/dashboard/metrics');
        
        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('kpis.active_jobs_count'));
        $this->assertEquals(0, $response->json('kpis.pending_action_count'));
    }
}
