<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiApiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY', ''));
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    }

    /**
     * Send a payload to the Gemini API and force a strict JSON response.
     * 
     * @param array $payload The conversation payload (constructed by ChatSessionManager)
     * @return array|null Parsed JSON response from Gemini, or null on failure.
     */
    public function generateHardwareRecommendation(array $payload): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('GEMINI_API_KEY is not set.');
            return null;
        }

        // Enforce JSON schema response via generationConfig
        if (!isset($payload['generationConfig'])) {
            $payload['generationConfig'] = [];
        }
        
        $payload['generationConfig']['responseMimeType'] = 'application/json';
        $payload['generationConfig']['responseSchema'] = [
            'type' => 'OBJECT',
            'properties' => [
                'components' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'category' => ['type' => 'STRING', 'description' => 'E.g., CPU, GPU, Motherboard, RAM, Storage, Case, PSU, Cooler'],
                            'recommended_specs' => ['type' => 'STRING', 'description' => 'Key specs to look for. E.g. "Core i5 or Ryzen 5", "DDR5 32GB", "RTX 4060"'],
                            'budget_allocation' => ['type' => 'NUMBER', 'description' => 'Estimated budget allocation for this component in USD']
                        ],
                        'required' => ['category', 'recommended_specs', 'budget_allocation']
                    ]
                ],
                'explanation' => [
                    'type' => 'STRING',
                    'description' => 'A conversational explanation of why these components were chosen.'
                ]
            ],
            'required' => ['components', 'explanation']
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '?key=' . $this->apiKey, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $jsonText = $data['candidates'][0]['content']['parts'][0]['text'];
                    return json_decode($jsonText, true);
                }
            } else {
                Log::error('Gemini API Error', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Gemini API Exception', ['message' => $e->getMessage()]);
        }

        return null;
    }
}
