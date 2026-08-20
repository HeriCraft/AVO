<?php

namespace App\AI\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobTagsGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $jobId,
        public array $tags
    ) {}
}
