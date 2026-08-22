<?php

namespace App\Persistence\Listeners;

use App\AI\Events\TagCreatedEvent;
use App\Persistence\Models\JobPost;
use Illuminate\Support\Facades\Log;

class UpdateJobTagsListener
{
    public function handle(TagCreatedEvent $event): void
    {
        try {
            JobPost::where('id', $event->job_id)->update(['tags' => $event->tags]);
        } catch (\Exception $e) {
            Log::error("Failed to update tags for job {$event->job_id}: " . $e->getMessage());
        }
    }
}
