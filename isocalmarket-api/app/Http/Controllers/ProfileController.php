<?php

namespace App\Http\Controllers;

use App\Infrastructure\Security\Google2FA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Cập nhật thông tin cơ bản (chỉ cho phép số điện thoại)
     */
    public function update(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
        ]);

        $userModel = Auth::user();
        if (!$userModel) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $userModel->phone = $request->phone;
        $userModel->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $userModel->id,
                'name' => $userModel->name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'role' => $userModel->role,
                'google2fa_enabled' => $userModel->google2fa_enabled,
            ]
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
        $userModel->save();

        return response()->json([
            'message' => 'Password updated successfully.'
        ]);
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

        // Sinh khóa bí mật mới
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
            'user' => [
                'id' => $userModel->id,
                'name' => $userModel->name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'role' => $userModel->role,
                'google2fa_enabled' => $userModel->google2fa_enabled,
            ]
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
            'user' => [
                'id' => $userModel->id,
                'name' => $userModel->name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'role' => $userModel->role,
                'google2fa_enabled' => $userModel->google2fa_enabled,
            ]
        ]);
    }
}
