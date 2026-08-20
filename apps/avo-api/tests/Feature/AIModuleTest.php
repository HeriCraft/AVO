<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;
use App\Jobs\Events\JobPostCreated;
use App\AI\Events\JobTagsGenerated;
use App\AI\Listeners\GenerateJobTagsListener;
use App\Jobs\Listeners\UpdateJobWithTagsListener;
use Illuminate\Support\Facades\Queue;
use App\AI\Services\GeminiClient;

class AIModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_module_listens_to_job_created_and_generates_tags()
    {
        Event::fake([JobTagsGenerated::class]);
        putenv('GEMINI_API_KEY=test_key');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '["AI", "Vue", "Test", "Event"]']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $listener = new GenerateJobTagsListener(new GeminiClient());
        
        $jobId = 123;
        $description = 'Test job description';
        $event = new JobPostCreated($jobId, $description);

        $listener->handle($event);

        Event::assertDispatched(JobTagsGenerated::class, function ($event) use ($jobId) {
            return $event->jobId === $jobId && $event->tags === ["AI", "Vue", "Test", "Event"];
        });
    }

    public function test_jobs_module_listens_to_tags_generated_and_updates_db()
    {
        $user = User::factory()->create(['role' => 'USER']);
        $job = JobPost::create([
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'DRAFT',
            'user_id' => $user->id,
            'tags' => null
        ]);

        $listener = new UpdateJobWithTagsListener();
        
        $tags = ['Integration', 'Events', 'SaaS', 'B2B'];
        $event = new JobTagsGenerated($job->id, $tags);
        
        $listener->handle($event);

        $this->assertEquals($tags, $job->fresh()->tags);
    }
}
