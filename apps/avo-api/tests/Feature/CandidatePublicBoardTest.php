<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;

class CandidatePublicBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_published_job_publicly()
    {
        $user = User::factory()->create();
        
        $job = JobPost::create([
            'title' => 'Software Engineer',
            'description' => 'Great position',
            'status' => 'PUBLISHED',
            'user_id' => $user->id,
            'tags' => ['Tech', 'Engineering']
        ]);

        $response = $this->getJson("/api/public/jobs/{$job->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('title', 'Software Engineer')
                 ->assertJsonPath('status', 'PUBLISHED');
    }

    public function test_cannot_fetch_draft_job_publicly()
    {
        $user = User::factory()->create();
        
        $job = JobPost::create([
            'title' => 'Secret Project Manager',
            'description' => 'Draft position',
            'status' => 'DRAFT',
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("/api/public/jobs/{$job->id}");
        $response->assertStatus(404);
    }
    
    public function test_returns_404_for_non_existent_job()
    {
        $response = $this->getJson("/api/public/jobs/9999");
        $response->assertStatus(404);
    }
}
