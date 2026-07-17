<?php

namespace App\Infrastructure\Security;

class Google2FA
{
    /**
     * Sinh khóa bí mật ngẫu nhiên Base32 (16 ký tự)
     */
    public static function generateSecretKey(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Tạo OTPAuth URL để vẽ QR Code quét Google Authenticator
     */
    public static function getQrCodeUrl(string $email, string $secret, string $issuer = 'Isocalmarket'): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($email) . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer);
    }

    /**
     * Xác minh mã OTP 6 số
     */
    public static function verifyCode(string $secret, string $code): bool
    {
        // Decode base32 secret
        $key = self::base32Decode($secret);
        if (!$key) {
            return false;
        }

        // Window thời gian (mỗi slice 30s)
        $timeSlice = floor(time() / 30);

        // Cho phép lệch trước/sau 1 time slice (30s) để bù đắp sai số đồng hồ client-server
        for ($i = -1; $i <= 1; $i++) {
            $slice = $timeSlice + $i;
            
            // Pack slice thời gian về chuỗi nhị phân 64-bit
            $time = pack('N*', 0) . pack('N*', $slice);
            
            // Tính toán HMAC-SHA1
            $hmac = hash_hmac('sha1', $time, $key, true);
            
            $offset = ord(substr($hmac, -1)) & 0x0F;
            
            // Trích xuất 4 byte từ offset
            $hashpart = substr($hmac, $offset, 4);
            
            // Unpack giá trị số nguyên 32-bit
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

    /**
     * Giải mã chuỗi Base32 về dữ liệu nhị phân
     */
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
