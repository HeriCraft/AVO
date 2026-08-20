<?php

namespace App\Jobs\Listeners;

use App\AI\Events\JobTagsGenerated;
use App\Persistence\Models\JobPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateJobWithTagsListener
{
    public function handle(JobTagsGenerated $event): void
    {
        try {
            DB::transaction(function () use ($event) {
                $job = JobPost::find($event->jobId);
                
                if ($job) {
                    $job->update(['tags' => $event->tags]);
                }
            });
        } catch (\Exception $e) {
            Log::error("Failed to update job {$event->jobId} with AI tags: " . $e->getMessage());
        }
    }
}
