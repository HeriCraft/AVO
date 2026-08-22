<?php

namespace App\AI\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Jobs\Events\JobPublishedEvent;
use App\AI\Listeners\GenerateTagsForJobListener;

use App\Candidates\Events\ResumeExtractedEvent;
use App\AI\Listeners\ProcessResumeAIListener;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            JobPublishedEvent::class,
            GenerateTagsForJobListener::class,
        );

        Event::listen(
            ResumeExtractedEvent::class,
            ProcessResumeAIListener::class,
        );
    }
}
