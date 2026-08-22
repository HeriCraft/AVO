<?php

namespace App\Candidates\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use App\Candidates\Events\ResumeExtractedEvent;
use App\Candidates\Events\CandidateVerifiedEvent;
use App\Persistence\Models\Candidate;
use App\Persistence\Models\Application;
use Illuminate\Support\Facades\Storage;

class ApplyController extends Controller
{
    /**
     * Step 1: Upload CV and begin AI extraction asynchronously.
     */
    public function upload(Request $request, $job_id)
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);

        $file = $request->file('resume');
        
        $trackingId = Str::uuid()->toString();
        $path = $file->storeAs('resumes', "{$trackingId}." . $file->extension());

        ResumeExtractedEvent::dispatch($trackingId, $path, $job_id);

        return response()->json([
            'tracking_id' => $trackingId,
            'message' => 'File uploaded. Processing...'
        ], 202);
    }

    /**
     * Check if AI parsing is complete and get pre-filled data.
     */
    public function status($tracking_id)
    {
        // Check if Persistence listener has created the draft candidate yet
        $candidate = Candidate::with(['experiences', 'educations'])->where('tracking_id', $tracking_id)->first();
        
        if (!$candidate) {
            return response()->json([
                'status' => 'processing'
            ], 200);
        }

        return response()->json([
            'status' => 'ready',
            'data' => $candidate
        ], 200);
    }

    /**
     * Step 2: Validate parsed data, update candidate, and transition application to NEW.
     */
    public function validateData(Request $request, $tracking_id)
    {
        $request->validate([
            'firstname' => 'required|string',
            'email' => 'required|email',
        ]);

        $candidate = Candidate::where('tracking_id', $tracking_id)->firstOrFail();

        // Update basic candidate data
        $candidate->update([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'email' => $request->input('email'),
            'phone_number' => $request->input('phone_number'),
            'summary' => $request->input('summary'),
            'address' => $request->input('address'),
            'country' => $request->input('country'),
        ]);

        // Replace experiences (simplified for hackathon/demo: delete old, create new)
        $candidate->experiences()->delete();
        if (is_array($request->input('experiences'))) {
            foreach ($request->input('experiences') as $exp) {
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

        // Replace educations
        $candidate->educations()->delete();
        if (is_array($request->input('educations'))) {
            foreach ($request->input('educations') as $edu) {
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

        // Upgrade Application from DRAFT to NEW (PENDING_HUMAN_REVIEW)
        // By default it was created as DRAFT.
        $application = Application::where('candidate_id', $candidate->id)->first();
        if ($application) {
            $application->update([
                'status' => 'PENDING_HUMAN_REVIEW',
                'ai_score' => $this->calculateBasicAiScore($candidate) // Mock score or default
            ]);
            CandidateVerifiedEvent::dispatch($tracking_id, $application->job_post_id);
        }

        return response()->json([
            'message' => 'Application submitted successfully.'
        ], 200);
    }

    private function calculateBasicAiScore(Candidate $candidate)
    {
        // Basic mock logic for now since we don't have a complex scoring AI running on validation
        $expCount = $candidate->experiences()->count();
        if ($expCount > 3) return 'GREEN';
        if ($expCount > 1) return 'YELLOW';
        return 'RED';
    }
}
