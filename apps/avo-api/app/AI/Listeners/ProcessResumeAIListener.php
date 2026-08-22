<?php

namespace App\AI\Listeners;

use App\Candidates\Events\ResumeExtractedEvent;
use App\AI\Events\ResumeParsedByAIEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessResumeAIListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ResumeExtractedEvent $event): void
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('GEMINI_API_KEY missing for resume parsing.');
            return;
        }

        try {
            $fileContents = Storage::get($event->file_path);
            if (!$fileContents) {
                Log::error('Resume file not found: ' . $event->file_path);
                return;
            }

            $base64Data = base64_encode($fileContents);
            $mimeType = str_ends_with(strtolower($event->file_path), '.pdf') ? 'application/pdf' : 'text/plain';

            $prompt = <<<PROMPT
You are an expert HR assistant. Extract the candidate's resume data from the document into the STRICT JSON format below. Do not add markdown blocks like ```json, just output the raw JSON object. If a field cannot be found, output null.

{
  "profile": { "firstname": "str", "lastname": "str", "email": "str|null", "phone_number": "str|null", "summary": "str|null", "address": "str|null", "country": "str|null" },
  "experiences": [ { "company": "str", "location": "str|null", "contract_type": "str|null", "from": "MM/yyyy", "to": "MM/yyyy|present", "description": "str|null" } ],
  "educations": [ { "institute": "str", "field": "str", "degree": "str", "from": "MM/yyyy", "to": "MM/yyyy|present", "description": "str|null" } ],
  "tags": ["array", "of", "strings"],
  "others": [ { "key": "section name", "value": "content" } ]
}
PROMPT;

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key={$apiKey}";
            
            $response = Http::timeout(120)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Data
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text', '');
                $content = preg_replace('/```json|```/', '', trim($content));
                $data = json_decode($content, true);
                
                if (is_array($data)) {
                    ResumeParsedByAIEvent::dispatch($event->tracking_id, $data, $event->job_id);
                } else {
                    Log::error('Invalid JSON from Gemini: ' . $content);
                }
            } else {
                Log::error('Gemini Resume Parsing failed: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Exception in ProcessResumeAIListener: ' . $e->getMessage());
        }
    }
}
