<?php

namespace App\Jobs\Events;

use App\Persistence\Models\JobPost;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $job;

    /**
     * Create a new event instance.
     */
    public function __construct(JobPost $job)
    {
        $this->job = $job;
    }
}
