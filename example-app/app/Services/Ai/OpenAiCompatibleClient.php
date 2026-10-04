<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Talks to any provider exposing an OpenAI-compatible /chat/completions endpoint.
 */
class OpenAiCompatibleClient implements LlmClient
{
    public function __construct(
        private string $apiUrl,
        private ?string $apiKey,
        private ?string $model,
        private int $timeout = 90,
    ) {}

    public function complete(string $systemPrompt, string $userMessage): string
    {
        if (blank($this->model)) {
            throw new AiGenerationException('The AI model is not configured. Please set AI_MODEL in the .env file.');
        }

        try {
            $response = Http::withToken((string) $this->apiKey)
                ->acceptJson()
                ->timeout($this->timeout)
                ->post(rtrim($this->apiUrl, '/').'/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('AI provider connection failed', ['message' => $exception->getMessage()]);

            throw new AiGenerationException('Could not connect to the AI service. Please check your internet connection and try again.');
        }

        if ($response->failed()) {
            Log::warning('AI provider returned an error', ['status' => $response->status(), 'body' => $response->body()]);

            throw new AiGenerationException('The AI service returned an error (HTTP '.$response->status().'). Please try again later.');
        }

        $text = $response->json('choices.0.message.content');

        if (! is_string($text) || trim($text) === '') {
            throw new AiGenerationException('The AI service returned an empty response. Please try again.');
        }

        return $text;
    }

    public function name(): string
    {
        return 'openai:'.$this->model;
    }
}
