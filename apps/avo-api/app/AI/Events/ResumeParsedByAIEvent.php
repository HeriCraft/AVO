<?php

namespace App\AI\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResumeParsedByAIEvent
{
    use Dispatchable, SerializesModels;

    public string $tracking_id;
    public array $data;
    public int $job_id;

    public function __construct(string $tracking_id, array $data, int $job_id)
    {
        $this->tracking_id = $tracking_id;
        $this->data = $data;
        $this->job_id = $job_id;
    }
}
