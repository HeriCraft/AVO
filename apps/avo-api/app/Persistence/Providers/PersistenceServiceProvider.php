<?php

namespace App\Persistence\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\AI\Events\TagCreatedEvent;
use App\Persistence\Listeners\UpdateJobTagsListener;

use App\AI\Events\ResumeParsedByAIEvent;
use App\Persistence\Listeners\CreateDraftCandidateListener;

class PersistenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            TagCreatedEvent::class,
            UpdateJobTagsListener::class,
        );

        Event::listen(
            ResumeParsedByAIEvent::class,
            CreateDraftCandidateListener::class,
        );
    }
}
