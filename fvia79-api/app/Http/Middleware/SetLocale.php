<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['en', 'vi'];

    public function handle(Request $request, Closure $next): Response
    {
        $requestedLocale = $request->header('X-Locale')
            ?: $request->getPreferredLanguage(self::SUPPORTED_LOCALES)
            ?: config('app.locale');

        $locale = strtolower(substr((string) $requestedLocale, 0, 2));
        app()->setLocale(in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : config('app.fallback_locale'));

        return $next($request);
    }
}
