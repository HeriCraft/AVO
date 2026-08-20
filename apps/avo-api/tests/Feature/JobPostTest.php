<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;
use App\Jobs\Events\JobPostCreated;

class JobPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_job_with_cover_image_and_dispatch_event()
    {
        Storage::fake('public');
        Event::fake([JobPostCreated::class]);

        $user = User::factory()->create(['role' => 'USER']);

        $file = UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user, 'api')->postJson('/api/jobs', [
            'title' => 'Software Engineer',
            'description' => 'Great job',
            'status' => 'DRAFT',
            'cover_image' => $file,
        ]);

        $response->assertStatus(201);
        $jobId = $response->json('id');
        $job = JobPost::find($jobId);

        $this->assertNotNull($job->cover_image_path);
        Storage::disk('public')->assertExists($job->cover_image_path);

        Event::assertDispatched(JobPostCreated::class, function ($event) use ($job) {
            return $event->jobId === $job->id;
        });
    }
}
