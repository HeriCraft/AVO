<?php

namespace App\AI\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TagCreatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $job_id,
        public array $tags
    ) {}
}
