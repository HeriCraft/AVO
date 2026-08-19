<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;

class UserFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'USER']);
        
        $response = $this->actingAs($user, 'api')->getJson('/api/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_user_routes()
    {
        $admin = User::factory()->create(['role' => 'SUPER_ADMIN']);
        
        $response = $this->actingAs($admin, 'api')->getJson('/api/jobs');
        $response->assertStatus(403);
    }

    public function test_user_can_create_and_manage_own_jobs()
    {
        $user = User::factory()->create(['role' => 'USER']);
        
        // Create job
        $response = $this->actingAs($user, 'api')->postJson('/api/jobs', [
            'title' => 'Frontend Dev',
            'description' => 'Vue 3 expert',
            'status' => 'DRAFT'
        ]);
        $response->assertStatus(201);
        $jobId = $response->json('id');

        // Update job
        $response = $this->actingAs($user, 'api')->putJson("/api/jobs/{$jobId}", [
            'status' => 'PUBLISHED'
        ]);
        $response->assertStatus(200);

        // Delete job
        $response = $this->actingAs($user, 'api')->deleteJson("/api/jobs/{$jobId}");
        $response->assertStatus(204);
    }

    public function test_user_cannot_manage_other_users_jobs()
    {
        $user1 = User::factory()->create(['role' => 'USER']);
        $user2 = User::factory()->create(['role' => 'USER']);
        
        $job = JobPost::create([
            'title' => 'Backend Dev',
            'description' => 'PHP expert',
            'status' => 'DRAFT',
            'user_id' => $user1->id
        ]);

        // User2 trying to update User1's job
        $response = $this->actingAs($user2, 'api')->putJson("/api/jobs/{$job->id}", [
            'status' => 'PUBLISHED'
        ]);
        $response->assertStatus(404); // Using findOrFail with user_id returns 404

        // User2 trying to delete User1's job
        $response = $this->actingAs($user2, 'api')->deleteJson("/api/jobs/{$job->id}");
        $response->assertStatus(404);
    }
}
