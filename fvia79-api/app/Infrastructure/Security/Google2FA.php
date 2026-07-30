<?php

namespace App\Infrastructure\Security;

class Google2FA
{
    public static function generateSecretKey(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    public static function getQrCodeUrl(string $email, string $secret, string $issuer = 'Fvia79'): string
    {
        return 'otpauth://totp/'
            .rawurlencode($issuer)
            .':'
            .rawurlencode($email)
            .'?secret='
            .rawurlencode($secret)
            .'&issuer='
            .rawurlencode($issuer);
    }

    public static function verifyCode(string $secret, string $code): bool
    {
        $key = self::base32Decode($secret);
        if (!$key) {
            return false;
        }
        $timeSlice = floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            $slice = $timeSlice + $i;
            $time = pack('N*', 0) . pack('N*', $slice);

            $hmac = hash_hmac('sha1', $time, $key, true);

            $offset = ord(substr($hmac, -1)) & 0x0F;

            $hashpart = substr($hmac, $offset, 4);
            $value = unpack('N', $hashpart);
            $value = $value[1];

            $value = $value & 0x7FFFFFFF;

            $modulo = pow(10, 6);
            $calcCode = str_pad((string)($value % $modulo), 6, '0', STR_PAD_LEFT);

            if (hash_equals($calcCode, $code)) {
                return true;
            }
        }

        return false;
    }

    private static function base32Decode(string $secret): ?string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $map = array_flip(str_split($chars));

        $secret = strtoupper($secret);
        $secret = str_replace('=', '', $secret);

        $buf = '';
        $val = 0;
        $bits = 0;

        for ($i = 0; $i < strlen($secret); $i++) {
            $c = $secret[$i];
            if (!isset($map[$c])) {
                return null;
            }
            $val = ($val << 5) | $map[$c];
            $bits += 5;
            if ($bits >= 8) {
                $bits -= 8;
                $buf .= chr(($val >> $bits) & 0xFF);
            }
        }
        return $buf;
    }
}
