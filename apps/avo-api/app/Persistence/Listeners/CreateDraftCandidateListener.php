<?php

namespace App\Persistence\Listeners;

use App\AI\Events\ResumeParsedByAIEvent;
use App\Persistence\Models\Candidate;
use App\Persistence\Models\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDraftCandidateListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ResumeParsedByAIEvent $event): void
    {
        $data = $event->data;
        $profile = $data['profile'] ?? [];

        $candidate = Candidate::create([
            'tracking_id' => $event->tracking_id,
            'firstname' => $profile['firstname'] ?? 'Unknown',
            'lastname'  => $profile['lastname'] ?? 'Unknown',
            'summary'   => $profile['summary'] ?? null,
            'address'   => $profile['address'] ?? null,
            'country'   => $profile['country'] ?? null,
            'email'     => $profile['email'] ?? null,
            'phone_number' => $profile['phone_number'] ?? null,
        ]);

        if (!empty($data['experiences']) && is_array($data['experiences'])) {
            foreach ($data['experiences'] as $exp) {
                $candidate->experiences()->create([
                    'company' => $exp['company'] ?? 'Unknown',
                    'location' => $exp['location'] ?? null,
                    'contract_type' => $exp['contract_type'] ?? null,
                    'from' => $exp['from'] ?? null,
                    'to' => $exp['to'] ?? null,
                    'description' => $exp['description'] ?? null,
                ]);
            }
        }

        if (!empty($data['educations']) && is_array($data['educations'])) {
            foreach ($data['educations'] as $edu) {
                $candidate->educations()->create([
                    'institute' => $edu['institute'] ?? 'Unknown',
                    'field' => $edu['field'] ?? null,
                    'degree' => $edu['degree'] ?? null,
                    'from' => $edu['from'] ?? null,
                    'to' => $edu['to'] ?? null,
                    'description' => $edu['description'] ?? null,
                ]);
            }
        }

        // We also create a DRAFT/NEW Application so the job_id is saved
        Application::create([
            'job_post_id' => $event->job_id,
            'candidate_id' => $candidate->id,
            'status' => 'DRAFT', // Will be upgraded to NEW on validation
        ]);
    }
}
