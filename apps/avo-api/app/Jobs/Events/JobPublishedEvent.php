<?php

namespace App\Jobs\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobPublishedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $job_id,
        public string $title,
        public string $description
    ) {}
}
