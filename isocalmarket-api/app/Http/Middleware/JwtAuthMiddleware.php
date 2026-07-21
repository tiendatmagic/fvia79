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
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('access_token');
        $hasBearer = false;

        if (! $token) {
            // Hỗ trợ Authorization Header làm fallback (cho test API)
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
                $hasBearer = true;
            }
        }

        if ($token) {
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

            // Đăng nhập user vào Auth context của Laravel
            Auth::login($userModel);

            return $next($request);
        }

        // Fallback sang xác thực bằng B2B API Key (nếu không có JWT token)
        $apiKey = $request->query('api_key');
        if (! $apiKey) {
            $apiKey = $request->header('X-API-KEY');
        }
        if (! $apiKey) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && ! $hasBearer) {
                $apiKey = $authHeader;
            }
        }

        if ($apiKey) {
            $userModel = UserModel::where('api_key_hash', hash('sha256', $apiKey))->first();
            if ($userModel) {
                if ($userModel->status === 'banned') {
                    return response()->json(['message' => 'Your account has been banned.'], 403);
                }

                // Đăng nhập user vào Auth context của Laravel
                Auth::login($userModel);
                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthorized. Token or API Key missing.'], 401);
    }
}
