<?php

namespace App\Providers;

use App\Services\Ai\DemoLlmClient;
use App\Services\Ai\LlmClient;
use App\Services\Ai\OpenAiCompatibleClient;
use App\Support\BladeTranslator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LlmClient::class, fn (): LlmClient => match (config('ai.provider')) {
            'openai' => new OpenAiCompatibleClient(
                apiUrl: (string) config('ai.api_url'),
                apiKey: config('ai.api_key'),
                model: config('ai.model'),
                timeout: (int) config('ai.timeout'),
            ),
            default => new DemoLlmClient,
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        Paginator::defaultView('components.pagination');

        // Static English text in views becomes translatable (see BladeTranslator).
        Blade::prepareStringsForCompilationUsing(BladeTranslator::apply(...));
    }
}
