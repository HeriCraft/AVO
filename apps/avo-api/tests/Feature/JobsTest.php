<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\Job;
use App\Jobs\Events\JobPublished;
use Illuminate\Support\Facades\Event;

class JobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_jobs()
    {
        $user = User::factory()->create();
        Job::create([
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
        Event::fake();

        $user = User::factory()->create();

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
        ]);

        Event::assertNotDispatched(JobPublished::class);
    }

    public function test_publishing_a_job_fires_job_published_event()
    {
        Event::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/jobs', [
            'title' => 'AI Engineer',
            'description' => 'Train models.',
            'status' => 'PUBLISHED',
        ]);

        $response->assertStatus(201);

        Event::assertDispatched(JobPublished::class, function ($event) {
            return $event->job->title === 'AI Engineer';
        });
    }

    public function test_unauthenticated_users_cannot_access_jobs()
    {
        $this->getJson('/api/jobs')->assertStatus(401);
        $this->postJson('/api/jobs', [])->assertStatus(401);
    }
}
