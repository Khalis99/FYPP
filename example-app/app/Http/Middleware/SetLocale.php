<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the interface language (English or Bahasa Melayu).
 *
 * A signed-in user's saved language wins, so it follows them to any device. Everyone else
 * (for example on the login page) uses the choice kept in the session and in a long-lived cookie.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = collect([
            $request->user()?->locale,
            $request->session()->get('locale'),
            $request->cookie('locale'),
        ])->first(fn ($candidate): bool => is_string($candidate) && array_key_exists($candidate, config('schoolhub.locales')))
            ?? config('app.locale');

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
