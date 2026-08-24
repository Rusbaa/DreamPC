<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ChatSessionManager;
use App\Services\GeminiApiService;
use App\Services\RecommendationMapperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected ChatSessionManager $sessionManager;
    protected GeminiApiService $geminiApi;
    protected RecommendationMapperService $recommendationMapper;

    public function __construct(
        ChatSessionManager $sessionManager,
        GeminiApiService $geminiApi,
        RecommendationMapperService $recommendationMapper
    ) {
        $this->sessionManager = $sessionManager;
        $this->geminiApi = $geminiApi;
        $this->recommendationMapper = $recommendationMapper;
    }

    public function index(Request $request)
    {
        $chatSession = $this->sessionManager->getOrCreateSession();
        $messages = $chatSession->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat.index', compact('chatSession', 'messages'));
    }


    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessageText = trim($request->input('message'));
        $chatSession = $this->sessionManager->getOrCreateSession();

        $geminiPayload = $this->sessionManager->buildGeminiPayload(
            $chatSession,
            $userMessageText
        );

    
        $userMessage = $this->sessionManager->storeMessage(
            $chatSession,
            'user',
            $userMessageText
        );

        
        $nlpResult = $this->processWithAI($userMessageText, $geminiPayload);

        $botMessage = $this->sessionManager->storeMessage(
            $chatSession,
            'assistant',
            $nlpResult['text'],
            $nlpResult['payload'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'user_message' => [
                'id' => $userMessage->id,
                'sender' => 'user',
                'text' => $userMessage->message_text,
                'created_at' => $userMessage->created_at->format('H:i'),
            ],
            'bot_message' => [
                'id' => $botMessage->id,
                'sender' => 'assistant',
                'text' => $botMessage->message_text,
                'payload' => $botMessage->json_payload,
                'created_at' => $botMessage->created_at->format('H:i'),
            ],
        ]);
    }



    protected function processWithAI(string $input, array $payloadContext = []): array
    {
        
        $geminiResponse = $this->geminiApi->generateHardwareRecommendation($payloadContext);

        if ($geminiResponse && !empty($geminiResponse['components'])) {
            return $this->handleGeminiResponse($geminiResponse);
        }

        
        Log::warning(
            'Gemini API unavailable or returned empty response, using fallback pattern matching.'
        );

        return $this->fallbackPatternMatching($input);
    }


    protected function handleGeminiResponse(array $geminiResponse): array
    {
        try {
            
            $mapped = $this->recommendationMapper->mapRecommendationsToProducts($geminiResponse);

            $text = $mapped['explanation'] ?? 'Here are my hardware recommendations:';
            $payload = null;

            
            $matchedProducts = [];

            if (!empty($mapped['components'])) {
                foreach ($mapped['components'] as $component) {
                    if (!empty($component['matched_product'])) {
                        $matchedProducts[] = (object) $component['matched_product'];
                    }
                }
            }

            // Build the response with matched products.
            if (!empty($matchedProducts)) {
                // Verify compatibility.
                $productIds = collect($matchedProducts)->pluck('id')->toArray();
                $compatibilityEngine = app(\App\Services\CompatibilityEngine::class);
                $compatCheck = $compatibilityEngine->checkCompatibility($productIds);

                $totalPrice = collect($matchedProducts)->sum(
                    fn ($product) => (float) str_replace('$', '', $product->price)
                );

                
                $cardHtml = view('chat.partials.build-card', [
                    'products' => collect($matchedProducts),
                    'isCompatible' => $compatCheck['is_compatible'],
                    'incompatibilities' => $compatCheck['incompatibilities'],
                    'totalPrice' => $totalPrice,
                ])->render();

                $payload = [
                    'intent' => 'build_recommendation',
                    'card_html' => $cardHtml,
                    'suggested_products' => collect($matchedProducts)
                        ->map(fn ($product) => [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'brand' => $product->brand,
                        ])
                        ->toArray(),
                ];
            } else {
                $text .= "\n\nNote: I couldn't find exact matches for all components. "
                    . 'Please browse our catalog for alternatives.';
            }

            return [
                'text' => $text,
                'payload' => $payload,
            ];
        } catch (\Exception $e) {
            Log::error('Error handling Gemini response: ' . $e->getMessage());

            return $this->fallbackPatternMatching($input);
        }
    }


    protected function fallbackPatternMatching(string $input): array
    {
        $lower = strtolower($input);
        $payload = null;

        
        if (
            str_contains($lower, 'budget') ||
            str_contains($lower, 'build') ||
            str_contains($lower, '$') ||
            str_contains($lower, 'pc')
        ) {
            preg_match('/\$?(\d+)/', $input, $matches);
            $budget = isset($matches[1]) ? (float) $matches[1] : 1000;

            $recommendedProducts = Product::with('category')
                ->where('price', '<=', $budget)
                ->orderBy('price', 'desc')
                ->take(3)
                ->get();

            if ($recommendedProducts->isNotEmpty()) {
                $text = 'Based on your request, here are top component recommendations within your target range:';

                $compatibilityEngine = app(\App\Services\CompatibilityEngine::class);
                $compatCheck = $compatibilityEngine->checkCompatibility(
                    $recommendedProducts->pluck('id')->all()
                );

                $totalPrice = $recommendedProducts->sum('price');

                
                $cardHtml = view('chat.partials.build-card', [
                    'products' => $recommendedProducts,
                    'isCompatible' => $compatCheck['is_compatible'],
                    'incompatibilities' => $compatCheck['incompatibilities'],
                    'totalPrice' => $totalPrice,
                ])->render();

                $payload = [
                    'intent' => 'build_recommendation',
                    'card_html' => $cardHtml,
                    'suggested_products' => $recommendedProducts
                        ->map(fn ($product) => [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => '$' . number_format($product->price, 2),
                            'brand' => $product->brand,
                        ])
                        ->toArray(),
                ];
            } else {
                $text = 'I analyzed your build request. To optimize your hardware config, '
                    . 'check out our catalog filters for CPUs, GPUs, and Motherboards!';
            }
        }

        
        elseif (
            str_contains($lower, 'compat') ||
            str_contains($lower, 'fit') ||
            str_contains($lower, 'match')
        ) {
            $text = 'I am powered by our real-time Automated Hardware Compatibility Engine! '
                . 'I automatically check CPU socket matching, RAM generation (DDR4 vs DDR5), '
                . 'case clearances, and PSU TDP requirements.';
            $payload = ['intent' => 'compatibility_info'];
        }

       
        elseif (
            str_contains($lower, 'gpu') ||
            str_contains($lower, 'graphics') ||
            str_contains($lower, 'rtx') ||
            str_contains($lower, 'rx')
        ) {
            $text = 'When choosing a Graphics Card (GPU), ensure your Power Supply (PSU) '
                . 'has enough wattage (TDP * 1.25) and your PC Case has sufficient length clearance!';
            $payload = ['intent' => 'gpu_advice'];
        }

        
        else {
            $text = 'Hello! I am your AI Hardware Assistant. You can ask me for PC build '
                . 'recommendations, component compatibility checks, or hardware advice!';
            $payload = ['intent' => 'general_help'];
        }

        return [
            'text' => $text,
            'payload' => $payload,
        ];
    }
}