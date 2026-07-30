<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMembershipTiersFeatureEnabled
{

    public function handle(Request $request, Closure $next): Response
    {
        $enabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);

        if (! $enabled) {
            return response()->json([
                'message' => 'The Membership Tiers feature is disabled in the server environment configuration (.env FEATURE_MEMBERSHIP_TIERS_ENABLED=false).',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
