<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Infrastructure\Security\JwtService;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Auth;

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

        if (!$token) {
            // Hỗ trợ Authorization Header làm fallback (cho test API)
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) {
            return response()->json(['message' => 'Unauthorized. Token missing.'], 401);
        }

        $payload = $this->jwtService->validateToken($token);

        if (!$payload || empty($payload['sub'])) {
            return response()->json(['message' => 'Unauthorized. Invalid or expired token.'], 401);
        }

        $userModel = UserModel::find($payload['sub']);
        if (!$userModel) {
            return response()->json(['message' => 'Unauthorized. User not found.'], 401);
        }

        // Đăng nhập user vào Auth context của Laravel
        Auth::login($userModel);

        return $next($request);
    }
}
