<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;
use App\Jobs\Events\JobPublished;
use App\Jobs\Events\JobPostCreated;

class JobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_jobs()
    {
        $user = User::factory()->create(['role' => 'USER']);
        JobPost::create([
            'title' => 'Software Engineer',
            'description' => 'Build things.',
            'status' => 'PUBLISHED',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/jobs');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonPath('0.title', 'Software Engineer');
    }

    public function test_authenticated_user_can_create_a_job_in_draft_status()
    {
        $user = User::factory()->create(['role' => 'USER']);
        Event::fake([JobPostCreated::class]);

        $response = $this->actingAs($user, 'api')->postJson('/api/jobs', [
            'title' => 'Product Manager',
            'description' => 'Manage products.',
            'status' => 'DRAFT',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('title', 'Product Manager');

        $this->assertDatabaseHas('job_postings', [
            'title' => 'Product Manager',
            'status' => 'DRAFT',
            'user_id' => $user->id,
        ]);

        Event::assertDispatched(JobPostCreated::class);
    }

    public function test_publishing_a_job_fires_job_published_event()
    {
        Event::fake([JobPublished::class, JobPostCreated::class]);

        $user = User::factory()->create(['role' => 'USER']);

        $response = $this->actingAs($user, 'api')->postJson('/api/jobs', [
            'title' => 'AI Engineer',
            'description' => 'Train models.',
            'status' => 'PUBLISHED',
        ]);

        $response->assertStatus(201);

        Event::assertDispatched(JobPublished::class, function ($event) {
            return $event->job->title === 'AI Engineer';
        });
        Event::assertDispatched(JobPostCreated::class);
    }

    public function test_unauthenticated_users_cannot_access_jobs()
    {
        $response = $this->getJson('/api/jobs');
        $response->assertStatus(401);
        $this->postJson('/api/jobs', [])->assertStatus(401);
    }
}
