<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;
use App\Persistence\Models\Candidate;
use App\Persistence\Models\Application;

class RecruiterApplicationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruiter_only_sees_applications_for_their_own_jobs()
    {
        $recruiterA = clone User::factory()->create(['role' => 'USER']);
        $recruiterB = clone User::factory()->create(['role' => 'USER']);

        $jobA = JobPost::create(['user_id' => $recruiterA->id, 'title' => 'Job A', 'description' => 'A']);
        $jobB = JobPost::create(['user_id' => $recruiterB->id, 'title' => 'Job B', 'description' => 'B']);

        $candidateA = Candidate::create(['tracking_id' => 'tA', 'firstname' => 'A', 'lastname' => 'A', 'email' => 'a@a.com']);
        $candidateB = Candidate::create(['tracking_id' => 'tB', 'firstname' => 'B', 'lastname' => 'B', 'email' => 'b@b.com']);

        $appA = Application::create(['job_post_id' => $jobA->id, 'candidate_id' => $candidateA->id, 'status' => 'NEW', 'ai_score' => 'GREEN']);
        $appB = Application::create(['job_post_id' => $jobB->id, 'candidate_id' => $candidateB->id, 'status' => 'NEW', 'ai_score' => 'RED']);

        $response = $this->actingAs($recruiterA, 'api')->getJson('/api/applications');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $this->assertEquals($jobA->id, $response->json('0.job_post_id'));
        $this->assertEquals($candidateA->id, $response->json('0.candidate_id'));
        
        $responseB = $this->actingAs($recruiterB, 'api')->getJson('/api/applications');
        $responseB->assertStatus(200);
        $responseB->assertJsonCount(1);
        $this->assertEquals($jobB->id, $responseB->json('0.job_post_id'));
        $this->assertEquals($candidateB->id, $responseB->json('0.candidate_id'));
    }
}
