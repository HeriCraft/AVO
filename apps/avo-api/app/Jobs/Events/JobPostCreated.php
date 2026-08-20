<?php

namespace App\Jobs\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Persistence\Models\JobPost;

class JobPostCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $jobId,
        public string $description
    ) {}
}
