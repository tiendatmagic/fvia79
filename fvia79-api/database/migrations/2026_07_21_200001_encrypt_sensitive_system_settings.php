<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SENSITIVE_KEYS = [
        'scanner_api_key',
        'binance_api_key',
        'binance_api_secret',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            foreach ($this->sensitiveSettings() as $setting) {
                if ($setting->value === null || $this->isEncrypted($setting->value)) {
                    continue;
                }

                DB::table('system_settings')
                    ->where('key', $setting->key)
                    ->update(['value' => Crypt::encryptString($setting->value)]);
            }
        });

        $this->clearSettingCache();
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            foreach ($this->sensitiveSettings() as $setting) {
                if ($setting->value === null) {
                    continue;
                }

                try {
                    $value = Crypt::decryptString($setting->value);
                } catch (DecryptException) {
                    continue;
                }

                DB::table('system_settings')
                    ->where('key', $setting->key)
                    ->update(['value' => $value]);
            }
        });

        $this->clearSettingCache();
    }

    private function sensitiveSettings(): iterable
    {
        return DB::table('system_settings')
            ->whereIn('key', self::SENSITIVE_KEYS)
            ->get(['key', 'value']);
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }

    private function clearSettingCache(): void
    {
        foreach (self::SENSITIVE_KEYS as $key) {
            Cache::forget("system_setting:{$key}");
        }
        Cache::forget('system_settings_all');
    }
};
