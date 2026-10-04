<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | "demo"   - Offline template generator. No API key needed. Useful for
    |            demonstrations and development.
    | "openai" - Any provider exposing an OpenAI-compatible
    |            /chat/completions endpoint (OpenAI, Groq, OpenRouter,
    |            Together, a local Ollama or LM Studio server, etc.).
    |
    | New providers can be added by implementing App\Services\Ai\LlmClient
    | and registering them in AppServiceProvider.
    |
    */

    'provider' => env('AI_PROVIDER', 'demo'),

    'api_key' => env('AI_API_KEY'),

    'api_url' => env('AI_API_URL', 'https://api.openai.com/v1'),

    'model' => env('AI_MODEL'),

    'timeout' => (int) env('AI_TIMEOUT', 90),

];
