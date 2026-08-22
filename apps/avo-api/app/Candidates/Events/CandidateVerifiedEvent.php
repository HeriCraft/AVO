<?php

namespace App\Candidates\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CandidateVerifiedEvent
{
    use Dispatchable, SerializesModels;

    public string $tracking_id;
    public int $job_id;

    public function __construct(string $tracking_id, int $job_id)
    {
        $this->tracking_id = $tracking_id;
        $this->job_id = $job_id;
    }
}
