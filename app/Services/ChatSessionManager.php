<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Support\Str;

class ChatSessionManager
{
    /**
     * Retrieve an existing chat session or create a new one using the session token.
     *
     * @param string|null $sessionToken
     * @param int|null $userId
     * @return ChatSession
     */
    public function getOrCreateSession(?string $sessionToken = null, ?int $userId = null): ChatSession
    {
        if (!$sessionToken) {
            $sessionToken = session('chat_session_token');
            if (!$sessionToken) {
                $sessionToken = Str::uuid()->toString();
                session(['chat_session_token' => $sessionToken]);
            }
        }

        return ChatSession::firstOrCreate(
            ['session_token' => $sessionToken],
            ['user_id' => $userId ?? auth()->id(), 'title' => 'Hardware Assistant Chat']
        );
    }

    /**
     * Store a message in the specified chat session.
     *
     * @param ChatSession $session
     * @param string $sender 'user' or 'assistant'
     * @param string $text
     * @param array|null $payload
     * @return ChatMessage
     */
    public function storeMessage(ChatSession $session, string $sender, string $text, ?array $payload = null): ChatMessage
    {
        return $session->messages()->create([
            'sender' => $sender,
            'message_text' => $text,
            'json_payload' => $payload,
        ]);
    }

    /**
     * Construct the conversation payload for the Gemini API.
     * Retrieves up to the last 10 messages to maintain context for follow-up requests.
     *
     * @param ChatSession $session
     * @param string|null $newMessageText Optionally append a new user message before fetching from DB
     * @return array The payload structure required by Gemini API
     */
    public function buildGeminiPayload(ChatSession $session, ?string $newMessageText = null): array
    {
        // Retrieve the last 10 messages from the database
        $messages = $session->messages()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        $contents = [];

        foreach ($messages as $msg) {
            // Map our sender 'assistant' to Gemini's 'model' role
            $role = ($msg->sender === 'assistant' || $msg->sender === 'bot') ? 'model' : 'user';
            
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $msg->message_text]
                ]
            ];
        }

        // If a new message is provided but not yet stored, append it to the payload
        if ($newMessageText) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $newMessageText]
                ]
            ];
        }

        return [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => 'You are an intelligent PC Hardware Assistant for DreamPC. Provide helpful, accurate component compatibility and build advice. Remember user context like budget constraints or brand preferences.']
                ]
            ]
        ];
    }
}
