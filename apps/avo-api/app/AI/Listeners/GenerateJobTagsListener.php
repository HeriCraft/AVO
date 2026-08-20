<?php

namespace App\AI\Listeners;

use App\Jobs\Events\JobPostCreated;
use App\AI\Events\JobTagsGenerated;
use App\AI\Services\GeminiClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateJobTagsListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private GeminiClient $geminiClient
    ) {}

    public function handle(JobPostCreated $event): void
    {
        $tags = $this->geminiClient->generateTags($event->description);

        if (!empty($tags)) {
            JobTagsGenerated::dispatch($event->jobId, $tags);
        } else {
            // Even if empty, dispatch an empty array to signal completion 
            // so UI doesn't spin forever, or we handle it based on domain rules.
            JobTagsGenerated::dispatch($event->jobId, []);
        }
    }
}
