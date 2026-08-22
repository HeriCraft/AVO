<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\JobPost;
use App\Jobs\Events\JobPublishedEvent;
use App\AI\Events\TagCreatedEvent;
use App\AI\Listeners\GenerateTagsForJobListener;
use App\Persistence\Listeners\UpdateJobTagsListener;

class EventDrivenTaggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_controller_dispatches_job_published_event()
    {
        Event::fake([JobPublishedEvent::class]);

        $user = User::factory()->create(['role' => 'USER']);

        $response = $this->actingAs($user, 'api')->postJson('/api/jobs', [
            'title' => 'Vue.js Developer',
            'description' => 'A great Vue.js frontend position',
            'status' => 'PUBLISHED',
        ]);

        $response->assertStatus(201);
        
        $jobId = $response->json('id');

        Event::assertDispatched(JobPublishedEvent::class, function ($event) use ($jobId) {
            return $event->job_id === $jobId 
                && $event->title === 'Vue.js Developer'
                && $event->description === 'A great Vue.js frontend position';
        });
    }

    public function test_ai_module_listens_to_job_published_and_emits_tag_created()
    {
        Event::fake([TagCreatedEvent::class]);
        putenv('GEMINI_API_KEY=fake_key');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '["Vue.js", "Frontend", "JavaScript", "Remote"]']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $listener = new GenerateTagsForJobListener();
        $event = new JobPublishedEvent(99, 'Vue.js Developer', 'A great Vue.js frontend position');

        $listener->handle($event);

        Event::assertDispatched(TagCreatedEvent::class, function ($event) {
            return $event->job_id === 99 && $event->tags === ["Vue.js", "Frontend", "JavaScript", "Remote"];
        });
    }

    public function test_persistence_module_updates_database_on_tag_created()
    {
        $user = User::factory()->create(['role' => 'USER']);
        $job = JobPost::create([
            'title' => 'Backend Go',
            'description' => 'Go developer needed',
            'status' => 'PUBLISHED',
            'user_id' => $user->id,
            'tags' => null
        ]);

        $listener = new UpdateJobTagsListener();
        $event = new TagCreatedEvent($job->id, ["Go", "Backend", "API", "Microservices"]);

        $listener->handle($event);

        $this->assertEquals(["Go", "Backend", "API", "Microservices"], $job->fresh()->tags);
    }
}
