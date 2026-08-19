<?php

namespace App\Jobs;

use App\Persistence\Models\JobPost;
use App\Jobs\Actions\GenerateJobTagsAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateTagsForJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public JobPost $jobPost)
    {
    }

    public function handle(GenerateJobTagsAction $action): void
    {
        $action->execute($this->jobPost);
    }
}
