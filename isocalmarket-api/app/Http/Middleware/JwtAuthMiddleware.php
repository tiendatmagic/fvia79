<?php

namespace App\Http\Middleware;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    public function __construct(private JwtService $jwtService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('access_token');

        if (! $token) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        if (! $token) {
            return response()->json(['message' => 'Unauthorized. JWT token missing.'], 401);
        }

        $payload = $this->jwtService->validateToken($token);
        if (! $payload || empty($payload['sub'])) {
            return response()->json(['message' => 'Unauthorized. Invalid or expired token.'], 401);
        }

        $userModel = UserModel::find($payload['sub']);
        if (! $userModel) {
            return response()->json(['message' => 'Unauthorized. User not found.'], 401);
        }

        if ((int) ($payload['ver'] ?? 0) !== (int) $userModel->token_version) {
            return response()->json(['message' => 'Unauthorized. Token has been revoked.'], 401);
        }

        if ($userModel->status === 'banned') {
            return response()->json(['message' => 'Your account has been banned.'], 403);
        }

        Auth::login($userModel);

        return $next($request);
    }
}
