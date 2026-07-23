<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Auth;

class B2bAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->query('api_key');

        if (!$apiKey) {
            $apiKey = $request->header('X-API-KEY');
        }

        if (!$apiKey) {
            $authHeader = $request->header('Authorization');
            if ($authHeader) {
                if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                    $apiKey = $matches[1];
                } else {
                    $apiKey = $authHeader;
                }
            }
        }

        if (!$apiKey) {
            return response()->json(['message' => 'Unauthorized. API Key is missing.'], 401);
        }

        $userModel = UserModel::where('api_key_hash', hash('sha256', $apiKey))->first();
        if (!$userModel) {
            return response()->json(['message' => 'Unauthorized. Invalid API Key.'], 401);
        }

        if ($userModel->status === 'banned') {
            return response()->json(['message' => 'Your account has been banned.'], 403);
        }

        Auth::login($userModel);

        return $next($request);
    }
}
