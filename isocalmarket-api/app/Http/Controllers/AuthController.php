<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Application\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authService->register(
                $request->username,
                $request->name,
                $request->email,
                $request->password
            );

            return response()->json([
                'message' => 'Registration successful.',
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = $this->authService->login($request->email, $request->password);
            if (!$user) {
                return response()->json(['message' => 'Invalid email/username or password.'], 401);
            }
            if ($user->isGoogle2faEnabled()) {
                $tempToken = $this->authService->generateTemp2FaToken($user);
                return response()->json([
                    'requires_2fa' => true,
                    'temp_token' => $tempToken
                ]);
            }

            $accessToken = $this->authService->generateAccessToken($user);
            $refreshToken = $this->authService->generateRefreshToken($user);

            $isProduction = config('app.env') === 'production';

            // Access token cookie: 30 phút
            $accessTokenCookie = cookie(
                'access_token',
                $accessToken,
                30,
                '/',
                null,
                $isProduction,
                true,  // HttpOnly
                false,
                'Lax'
            );

            // Refresh token cookie: 7 ngày (10080 phút)
            $refreshTokenCookie = cookie(
                'refresh_token',
                $refreshToken,
                10080,
                '/api/v1/auth/refresh',
                null,
                $isProduction,
                true,  // HttpOnly
                false,
                'Lax'
            );

            $loggedInCookie = cookie(
                'auth_logged_in',
                'true',
                10080,
                '/',
                null,
                $isProduction,
                false, // Không HttpOnly để JS đọc được
                false,
                'Lax'
            );

            return response()->json([
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'role' => $user->getRole(),
                    'google2fa_enabled' => $user->isGoogle2faEnabled(),
                    'balance' => $user->getBalance(),
                    'api_key' => $user->getApiKey()
                ]
            ])->withCookie($accessTokenCookie)
                ->withCookie($refreshTokenCookie)
                ->withCookie($loggedInCookie);
        } catch (\Exception $e) {
            $code = str_contains($e->getMessage(), 'banned') ? 403 : 500;
            return response()->json(['message' => $e->getMessage()], $code);
        }
    }

    public function login2Fa(Request $request)
    {
        $request->validate([
            'temp_token' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        try {
            $userId = $this->authService->validateTemp2FaToken($request->temp_token);
            if (!$userId) {
                return response()->json(['message' => 'Invalid or expired verification token.'], 401);
            }

            $user = $this->authService->getUserById($userId);
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $verified = \App\Infrastructure\Security\Google2FA::verifyCode($user->getGoogle2faSecret(), $request->code);
            if (!$verified) {
                return response()->json(['message' => 'Invalid OTP code. Please try again.'], 400);
            }

            $accessToken = $this->authService->generateAccessToken($user);
            $refreshToken = $this->authService->generateRefreshToken($user);

            $isProduction = config('app.env') === 'production';

            $accessTokenCookie = cookie(
                'access_token',
                $accessToken,
                30,
                '/',
                null,
                $isProduction,
                true,
                false,
                'Lax'
            );

            $refreshTokenCookie = cookie(
                'refresh_token',
                $refreshToken,
                10080,
                '/api/v1/auth/refresh',
                null,
                $isProduction,
                true,
                false,
                'Lax'
            );

            $loggedInCookie = cookie(
                'auth_logged_in',
                'true',
                10080,
                '/',
                null,
                $isProduction,
                false,
                false,
                'Lax'
            );

            return response()->json([
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'role' => $user->getRole(),
                    'google2fa_enabled' => $user->isGoogle2faEnabled(),
                    'balance' => $user->getBalance(),
                    'api_key' => $user->getApiKey()
                ]
            ])->withCookie($accessTokenCookie)
                ->withCookie($refreshTokenCookie)
                ->withCookie($loggedInCookie);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Unauthorized. Refresh token missing.'], 401);
        }

        $result = $this->authService->refresh($refreshToken);

        if (!$result) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $isProduction = config('app.env') === 'production';

        $accessTokenCookie = cookie(
            'access_token',
            $result['access_token'],
            30,
            '/',
            null,
            $isProduction,
            true,
            false,
            'Lax'
        );

        $refreshTokenCookie = cookie(
            'refresh_token',
            $result['refresh_token'],
            10080,
            '/api/v1/auth/refresh',
            null,
            $isProduction,
            true,
            false,
            'Lax'
        );

        $user = $result['user'];

        return response()->json([
            'message' => 'Token refreshed.',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'role' => $user->getRole(),
                'google2fa_enabled' => $user->isGoogle2faEnabled(),
                'balance' => $user->getBalance(),
                'api_key' => $user->getApiKey()
            ]
        ])->withCookie($accessTokenCookie)->withCookie($refreshTokenCookie);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $this->authService->logout($user->id);
        }

        // Xóa cookie bằng cách thiết lập cookie hết hạn
        $accessTokenCookie = cookie()->forget('access_token');
        $refreshTokenCookie = cookie()->forget('refresh_token');
        $loggedInCookie = cookie()->forget('auth_logged_in');

        return response()->json(['message' => 'Logout successful.'])
            ->withCookie($accessTokenCookie)
            ->withCookie($refreshTokenCookie)
            ->withCookie($loggedInCookie);
    }

    public function me(Request $request)
    {
        $userModel = Auth::user();
        if (!$userModel) {
            return response()->json(['message' => 'Not logged in.'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $userModel->id,
                'username' => $userModel->username,
                'name' => $userModel->name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'role' => $userModel->role,
                'google2fa_enabled' => $userModel->google2fa_enabled,
                'balance' => (float)$userModel->balance,
                'api_key' => $userModel->api_key
            ]
        ]);
    }
}
