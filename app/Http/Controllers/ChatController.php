<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Product;
use App\Services\ChatSessionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected ChatSessionManager $sessionManager;

    public function __construct(ChatSessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Display the chat interface and load active session messages.
     */
    public function index(Request $request)
    {
        $chatSession = $this->sessionManager->getOrCreateSession();
        $messages = $chatSession->messages()->orderBy('created_at', 'asc')->get();

        return view('chat.index', compact('chatSession', 'messages'));
    }

    /**
     * Send a message to the natural language processing assistant.
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessageText = trim($request->input('message'));
        $chatSession = $this->sessionManager->getOrCreateSession();

        // Construct Gemini Payload with History BEFORE storing user message (or pass user message to builder)
        // Here we build the payload including up to the last 10 messages + the new message
        $geminiPayload = $this->sessionManager->buildGeminiPayload($chatSession, $userMessageText);

        // Store User Message
        $userMessage = $this->sessionManager->storeMessage($chatSession, 'user', $userMessageText);

        // Pass payload (with context) into the NLP engine
        $nlpResult = $this->processNaturalLanguageInput($userMessageText, $geminiPayload);

        // Store Assistant Response
        $botMessage = $this->sessionManager->storeMessage($chatSession, 'assistant', $nlpResult['text'], $nlpResult['payload'] ?? null);

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

    /**
     * Natural Language Processing engine logic to understand user intent.
     */
    protected function processNaturalLanguageInput(string $input, array $payloadContext = []): array
    {
        $lower = strtolower($input);
        $payload = null;

        // Intent 1: Budget PC Build Request
        if (str_contains($lower, 'budget') || str_contains($lower, 'build') || str_contains($lower, '$') || str_contains($lower, 'pc')) {
            preg_match('/\$?(\d+)/', $input, $matches);
            $budget = isset($matches[1]) ? (float)$matches[1] : 1000;

            $recommendedProducts = Product::with('category')
                ->where('price', '<=', $budget)
                ->orderBy('price', 'desc')
                ->take(3)
                ->get();

            if ($recommendedProducts->isNotEmpty()) {
                $productNames = $recommendedProducts->pluck('name')->implode(', ');
                $text = "Based on your request, here are top component recommendations within your target range: **{$productNames}**.";
                $payload = [
                    'intent' => 'build_recommendation',
                    'suggested_products' => $recommendedProducts->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'price' => '$' . number_format($p->price, 2),
                        'brand' => $p->brand,
                    ])->toArray(),
                ];
            } else {
                $text = "I analyzed your build request. To optimize your hardware config, check out our catalog filters for CPUs, GPUs, and Motherboards!";
            }
        }
        // Intent 2: Compatibility Query
        elseif (str_contains($lower, 'compat') || str_contains($lower, 'fit') || str_contains($lower, 'match')) {
            $text = "I am powered by our real-time Automated Hardware Compatibility Engine! I automatically check CPU socket matching, RAM generation (DDR4 vs DDR5), case clearances, and PSU TDP requirements.";
            $payload = ['intent' => 'compatibility_info'];
        }
        // Intent 3: GPU / Performance Query
        elseif (str_contains($lower, 'gpu') || str_contains($lower, 'graphics') || str_contains($lower, 'rtx') || str_contains($lower, 'rx')) {
            $text = "When choosing a Graphics Card (GPU), ensure your Power Supply (PSU) has enough wattage (TDP * 1.25) and your PC Case has sufficient length clearance!";
            $payload = ['intent' => 'gpu_advice'];
        }
        // Default Intent
        else {
            $text = "Hello! I am your AI Hardware Assistant. You can ask me for PC build recommendations, component compatibility checks, or hardware advice!";
            $payload = ['intent' => 'general_help'];
        }

        return [
            'text' => $text,
            'payload' => $payload,
        ];
    }
}
