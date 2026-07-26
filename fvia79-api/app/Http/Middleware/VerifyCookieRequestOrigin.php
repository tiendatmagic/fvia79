<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCookieRequestOrigin
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)
            || ! $request->hasCookie('access_token')) {
            return $next($request);
        }

        $origin = rtrim((string) $request->headers->get('Origin'), '/');
        $allowedOrigins = array_map(
            static fn (string $allowedOrigin): string => rtrim($allowedOrigin, '/'),
            array_filter(config('cors.allowed_origins', []), 'is_string')
        );

        $appUrl = rtrim((string) config('app.url'), '/');

        $isTrustedOrigin = $origin === ''
            || $origin === $appUrl
            || in_array($origin, $allowedOrigins, true);

        $isAjaxRequest = $request->ajax()
            || $request->headers->get('X-Requested-With') === 'XMLHttpRequest'
            || $request->expectsJson()
            || $request->isJson()
            || str_contains((string) $request->header('Accept'), 'application/json');

        if (! $isTrustedOrigin || ! $isAjaxRequest) {
            return response()->json(['message' => 'CSRF validation failed.'], 419);
        }

        return $next($request);
    }
}
