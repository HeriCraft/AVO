<?php

namespace App\Jobs\Actions;

use App\Persistence\Models\JobPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateJobTagsAction
{
    public function execute(JobPost $jobPost): void
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::warning('GEMINI_API_KEY not configured. Skipping tag generation.');
            return;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        
        $prompt = "Analyze the following job description and return exactly 4 relevant, professional keywords or short tags (e.g., 'Remote', 'Vue.js', 'B2B'). Return ONLY a valid JSON array of strings. Description: {$jobPost->description}";

        try {
            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text');
                
                // Clean the response to ensure it's purely JSON (sometimes Gemini wraps in ```json ... ```)
                $content = preg_replace('/```json|```/', '', $content);
                $tags = json_decode(trim($content), true);
                
                if (is_array($tags)) {
                    $jobPost->update(['tags' => $tags]);
                }
            } else {
                Log::error('Gemini API failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate job tags: ' . $e->getMessage());
        }
    }
}
