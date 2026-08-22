<?php

namespace App\AI\Listeners;

use App\Jobs\Events\JobPublishedEvent;
use App\AI\Events\TagCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateTagsForJobListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(JobPublishedEvent $event): void
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::warning('GEMINI_API_KEY not configured. Skipping tag generation.');
            TagCreatedEvent::dispatch($event->job_id, []);
            return;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key={$apiKey}";
        
        $prompt = "Analyze the following job title and description and return exactly 4 relevant, professional keywords or short tags. Return ONLY a valid JSON array of strings. Title: {$event->title} Description: {$event->description}";

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
                $content = $response->json('candidates.0.content.parts.0.text', '');
                $content = preg_replace('/```json|```/', '', $content);
                $tags = json_decode(trim($content), true);
                
                if (is_array($tags)) {
                    TagCreatedEvent::dispatch($event->job_id, $tags);
                    return;
                }
            } else {
                Log::error('Gemini API failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate job tags via Gemini: ' . $e->getMessage());
        }

        TagCreatedEvent::dispatch($event->job_id, ['Remote', 'PHP', 'Laravel', 'Vue']);
    }
}
