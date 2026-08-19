<?php

namespace App\Users\Listeners;

use App\Users\Events\UserLoggedIn;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LogUserActivityListener
{
    public function handle(UserLoggedIn $event)
    {
        DB::table('activity_logs')->insert([
            'user_id' => $event->user->id,
            'action' => 'LOGIN',
            'payload' => json_encode(['ip' => request()->ip()]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
