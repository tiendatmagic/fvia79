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
                    'uid' => $user->getUid(),
                    'username' => $user->getUsername(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                ]
            ], 201);
        } catch (\Exception $e) {
            if (in_array($e->getMessage(), ['Email already registered.', 'Username already taken.'], true)) {
                return response()->json(['message' => $e->getMessage()], 400);
            }

            return $this->serverError($e, 'Failed to register user.');
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

            $session = $this->authService->createSession($user, $request->ip(), $request->header('User-Agent'));
            $accessToken = $this->authService->generateAccessToken($user, $session->id);
            $refreshToken = $this->authService->generateRefreshToken($user);

            $isProduction = config('app.env') === 'production';
            $sameSite = $isProduction ? 'None' : 'Lax';

            $accessTokenCookie = cookie(
                'access_token',
                $accessToken,
                30,
                '/',
                null,
                $isProduction,
                true,  // HttpOnly
                false,
                $sameSite
            );

            $refreshTokenCookie = cookie(
                'refresh_token',
                $refreshToken,
                10080,
                '/api/v1/auth/refresh',
                null,
                $isProduction,
                true,  // HttpOnly
                false,
                $sameSite
            );
            return response()->json([
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user->getId(),
                    'uid' => $user->getUid(),
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
                ->withCookie($refreshTokenCookie);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Your account has been banned. Please contact administrator.') {
                return response()->json(['message' => $e->getMessage()], 403);
            }

            return $this->serverError($e, 'Failed to authenticate user.');
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to authenticate user.');
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

            if ($user->isBanned()) {
                return response()->json(['message' => 'Your account has been banned. Please contact administrator.'], 403);
            }

            $verified = \App\Infrastructure\Security\Google2FA::verifyCode($user->getGoogle2faSecret(), $request->code);
            if (!$verified) {
                return response()->json(['message' => 'Invalid OTP code. Please try again.'], 400);
            }

            $session = $this->authService->createSession($user, $request->ip(), $request->header('User-Agent'));
            $accessToken = $this->authService->generateAccessToken($user, $session->id);
            $refreshToken = $this->authService->generateRefreshToken($user);

            $isProduction = config('app.env') === 'production';
            $sameSite = $isProduction ? 'None' : 'Lax';

            $accessTokenCookie = cookie(
                'access_token',
                $accessToken,
                30,
                '/',
                null,
                $isProduction,
                true,
                false,
                $sameSite
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
                $sameSite
            );
            return response()->json([
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user->getId(),
                    'uid' => $user->getUid(),
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
                ->withCookie($refreshTokenCookie);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to complete 2FA login.');
        }
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token') ?? $request->input('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Unauthorized. Refresh token missing.'], 401);
        }

        $currentSessionId = $request->attributes->get('current_session_id');

        if (! $currentSessionId) {
            $token = $request->cookie('access_token');
            if (! $token) {
                $authHeader = $request->header('Authorization');
                if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                    $token = $matches[1];
                }
            }
            if ($token) {
                $payload = app(\App\Infrastructure\Security\JwtService::class)->extractPayload($token);
                if (! empty($payload['sid'])) {
                    $currentSessionId = $payload['sid'];
                }
            }
        }

        $result = $this->authService->refresh(
            $refreshToken,
            $currentSessionId,
            $request->ip(),
            $request->header('User-Agent')
        );

        if (!$result) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $isProduction = config('app.env') === 'production';
        $sameSite = $isProduction ? 'None' : 'Lax';

        $accessTokenCookie = cookie(
            'access_token',
            $result['access_token'],
            30,
            '/',
            null,
            $isProduction,
            true,
            false,
            $sameSite
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
            $sameSite
        );

        $user = $result['user'];

        return response()->json([
            'message' => 'Token refreshed.',
            'user' => [
                'id' => $user->getId(),
                'uid' => $user->getUid(),
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

            $currentSessionId = $request->attributes->get('current_session_id');
            if ($currentSessionId) {
                UserSessionModel::where('id', $currentSessionId)
                    ->where('user_id', $user->id)
                    ->delete();
            }
        }

        $isProduction = config('app.env') === 'production';
        $sameSite = $isProduction ? 'None' : 'Lax';

        $accessTokenCookie = cookie('access_token', '', -2628000, '/', null, $isProduction, true, false, $sameSite);
        $refreshTokenCookie = cookie('refresh_token', '', -2628000, '/api/v1/auth/refresh', null, $isProduction, true, false, $sameSite);

        return response()->json(['message' => 'Logout successful.'])
            ->withCookie($accessTokenCookie)
            ->withCookie($refreshTokenCookie);
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
                'uid' => $userModel->uid,
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
