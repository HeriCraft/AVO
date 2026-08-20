<?php

namespace App\AI\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Jobs\Events\JobPostCreated;
use App\AI\Listeners\GenerateJobTagsListener;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            JobPostCreated::class,
            GenerateJobTagsListener::class,
        );
    }
}
