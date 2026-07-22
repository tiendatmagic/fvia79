<?php

namespace App\Http\Controllers;

use App\Application\Services\AuthService;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use App\Infrastructure\Security\Google2FA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Cập nhật thông tin cơ bản (cho phép Họ tên và số điện thoại, KHÔNG cho phép đổi username/email)
     */
    public function update(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $userModel = Auth::user();
        if (!$userModel) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $userModel->name = $request->name;
        $userModel->phone = $request->phone;
        $userModel->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $this->serializeUser($userModel),
        ]);
    }

    /**
     * Thay đổi mật khẩu (3 trường)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $userModel = Auth::user();
        if (!$userModel) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if (!Hash::check($request->current_password, $userModel->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        $userModel->password = Hash::make($request->new_password);
        $userModel->token_version = ((int) $userModel->token_version) + 1;
        $userModel->save();

        // Tạo access_token và refresh_token mới cho phiên hiện tại
        $user = UserMapper::toDomain($userModel);
        $accessToken = $this->authService->generateAccessToken($user);
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
            'message' => 'Password updated successfully.',
            'user' => $this->serializeUser($userModel),
        ])->withCookie($accessTokenCookie)->withCookie($refreshTokenCookie);
    }

    /**
     * Yêu cầu kích hoạt 2FA (Sinh secret key)
     */
    public function enable2Fa(Request $request)
    {
        $userModel = Auth::user();
        if (!$userModel) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ($userModel->google2fa_enabled) {
            return response()->json(['message' => '2FA is already enabled.'], 400);
        }

        $secret = Google2FA::generateSecretKey();
        $userModel->google2fa_secret = $secret;
        $userModel->save();

        $qrCodeUrl = Google2FA::getQrCodeUrl($userModel->email, $secret);

        return response()->json([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl
        ]);
    }

    /**
     * Xác nhận kích hoạt 2FA bằng mã OTP
     */
    public function confirm2Fa(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userModel = Auth::user();
        if (!$userModel) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if (!$userModel->google2fa_secret) {
            return response()->json(['message' => '2FA has not been initialized. Please enable first.'], 400);
        }

        $verified = Google2FA::verifyCode($userModel->google2fa_secret, $request->code);

        if (!$verified) {
            return response()->json(['message' => 'Invalid OTP code. Please try again.'], 400);
        }

        $userModel->google2fa_enabled = true;
        $userModel->save();

        return response()->json([
            'message' => '2FA enabled successfully.',
            'user' => $this->serializeUser($userModel),
        ]);
    }

    public function disable2Fa(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userModel = Auth::user();
        if (!$userModel) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if (!$userModel->google2fa_secret) {
            return response()->json(['message' => '2FA is not enabled.'], 400);
        }

        $verified = Google2FA::verifyCode($userModel->google2fa_secret, $request->code);
        if (!$verified) {
            return response()->json(['message' => 'Invalid 2FA verification code.'], 400);
        }

        $userModel->google2fa_enabled = false;
        $userModel->google2fa_secret = null;
        $userModel->save();

        return response()->json([
            'message' => '2FA disabled successfully.',
            'user' => $this->serializeUser($userModel),
        ]);
    }

    /**
     * POST /v1/profile/api-key
     * Sinh/Làm mới API Key cho người dùng hiện tại
     */
    public function generateApiKey(Request $request)
    {
        $userModel = Auth::user();
        if (!$userModel) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $userModel->api_key = \App\Infrastructure\Persistence\Eloquent\UserModel::generateUniqueApiKey();
        $userModel->save();

        return response()->json([
            'message' => 'API Key generated successfully.',
            'user' => $this->serializeUser($userModel),
        ]);
    }

    private function serializeUser(UserModel $userModel): array
    {
        return [
            'id' => $userModel->id,
            'uid' => $userModel->uid,
            'username' => $userModel->username,
            'name' => $userModel->name,
            'email' => $userModel->email,
            'phone' => $userModel->phone,
            'role' => $userModel->role,
            'google2fa_enabled' => (bool) $userModel->google2fa_enabled,
            'balance' => (float) $userModel->balance,
            'api_key' => $userModel->api_key,
        ];
    }
}
