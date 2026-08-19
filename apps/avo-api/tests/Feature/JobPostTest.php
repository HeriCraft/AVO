<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;
use App\Jobs\GenerateTagsForJob;
use Illuminate\Support\Facades\Queue;

class JobPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_job_with_cover_image_and_dispatch_tags_job()
    {
        Storage::fake('public');
        Queue::fake();

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

        Queue::assertPushed(GenerateTagsForJob::class, function ($queuedJob) use ($job) {
            return $queuedJob->jobPost->id === $job->id;
        });
    }

    public function test_gemini_action_generates_and_saves_tags()
    {
        $user = User::factory()->create(['role' => 'USER']);
        $job = JobPost::create([
            'title' => 'Test',
            'description' => 'Test Desc',
            'status' => 'DRAFT',
            'user_id' => $user->id,
        ]);

        putenv('GEMINI_API_KEY=test');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '["Remote", "Vue.js", "B2B", "SaaS"]']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $action = new \App\Jobs\Actions\GenerateJobTagsAction();
        $action->execute($job);

        $this->assertEquals(["Remote", "Vue.js", "B2B", "SaaS"], $job->fresh()->tags);
    }
}
