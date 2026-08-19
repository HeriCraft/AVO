<?php

namespace App\Users\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Users\Listeners\LogUserActivityListener;

class UsersServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Event::subscribe(LogUserActivityListener::class);

        $this->loadRoutesFrom(__DIR__.'/../routes.php');
    }
}
