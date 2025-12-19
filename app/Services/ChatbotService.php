<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private string $baseUrl;
    private string $apiKey;
    private string $model;
    private float $temperature;
    private int $maxTokens;
    private string $systemPrompt;

    public function __construct()
    {
        // Load generic AI config (DeepSeek/Groq)
        $this->baseUrl = config('services.chat_ai.base_url', 'https://api.deepseek.com');
        $this->apiKey = config('services.chat_ai.api_key', '');
        $this->model = config('services.chat_ai.model', 'deepseek-chat');
        $this->temperature = (float) config('services.chat_ai.temperature', 0.7);
        $this->maxTokens = (int) config('services.chat_ai.max_tokens', 500);

        // Load system prompt from config
        $this->systemPrompt = config('chatbot_prompt.system_prompt', '');
    }

    /**
     * Generate AI response for user message
     */
    public function generateResponse(string $userMessage, array $conversationHistory = []): string
    {
        // Check if API key is configured
        if (empty($this->apiKey) || $this->apiKey === 'your_api_key_here') {
            Log::warning('AI API key not configured');
            return $this->getFallbackMessage();
        }

        try {
            // Build conversation messages
            $messages = $this->buildConversation($userMessage, $conversationHistory);

            // Call AI API
            $response = $this->callAiApi($messages);

            if (!$response) {
                return $this->getFallbackMessage();
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->getFallbackMessage();
        }
    }

    /**
     * Build conversation array for OpenAI-compatible API
     */
    private function buildConversation(string $userMessage, array $history): array
    {
        $messages = [];

        // Add system prompt
        if (!empty($this->systemPrompt)) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->systemPrompt
            ];
        }

        // Add conversation history
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'] === 'model' ? 'assistant' : 'user', // Map 'model' to 'assistant'
                'content' => $msg['message']
            ];
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        return $messages;
    }

    /**
     * Call Generic AI API (OpenAI Compatible)
     */
    private function callAiApi(array $messages): ?string
    {
        // Ensure base URL doesn't have trailing slash
        $baseUrl = rtrim($this->baseUrl, '/');
        $url = "{$baseUrl}/v1/chat/completions";

        try {
            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
            ];

            // DeepSeek specific: ensure stream is false (optional but explicitly safe)
            $payload['stream'] = false;

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                // Extract text from OpenAI standard response format
                if (isset($data['choices'][0]['message']['content'])) {
                    $text = $data['choices'][0]['message']['content'];

                    Log::info('AI response generated', [
                        'model' => $this->model,
                        'length' => strlen($text)
                    ]);

                    return trim($text);
                }

                Log::warning('Unexpected AI response format', ['data' => $data]);
                return null;
            }

            Log::error('AI API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('AI API call failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get fallback message when AI is unavailable
     */
    private function getFallbackMessage(): string
    {
        return "Mohon maaf, saya sedang mengalami gangguan teknis.\n\n"
            . "Untuk bantuan lebih lanjut, silakan hubungi tim support kami:\n"
            . "- Email: support@bbihub.com\n"
            . "- WhatsApp: 0812-xxxx-xxxx\n\n"
            . "Tim kami akan segera membantu Anda.";
    }
}
