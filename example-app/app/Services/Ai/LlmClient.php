<?php

namespace App\Services\Ai;

/**
 * A provider-neutral text generation client.
 *
 * Feature services (e.g. RphGenerationService) depend on this interface only,
 * so the underlying provider can be swapped through config/ai.php.
 */
interface LlmClient
{
    /**
     * Send a system prompt and user message, returning the model's text reply.
     *
     * @throws AiGenerationException
     */
    public function complete(string $systemPrompt, string $userMessage): string;

    /**
     * Short name stored with generated documents, e.g. "openai:model-name".
     */
    public function name(): string;
}
