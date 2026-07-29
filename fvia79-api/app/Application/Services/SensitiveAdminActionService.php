<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\Google2FA;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SensitiveAdminActionService
{
    public function authorize(UserModel $user, string $password, ?string $otpCode): void
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The current password is incorrect.'],
            ]);
        }

        if (! $user->google2fa_enabled) {
            return;
        }

        if (! $otpCode || ! Google2FA::verifyCode((string) $user->google2fa_secret, $otpCode)) {
            throw ValidationException::withMessages([
                'otp_code' => ['The two-factor authentication code is invalid.'],
            ]);
        }
    }
}
