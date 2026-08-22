<?php

namespace App\Candidates\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResumeExtractedEvent
{
    use Dispatchable, SerializesModels;

    public string $tracking_id;
    public string $file_path;
    public int $job_id;

    public function __construct(string $tracking_id, string $file_path, int $job_id)
    {
        $this->tracking_id = $tracking_id;
        $this->file_path = $file_path;
        $this->job_id = $job_id;
    }
}
