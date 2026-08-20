<?php

namespace App\Jobs\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\AI\Events\JobTagsGenerated;
use App\Jobs\Listeners\UpdateJobWithTagsListener;

class JobsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            JobTagsGenerated::class,
            UpdateJobWithTagsListener::class,
        );
    }
}
