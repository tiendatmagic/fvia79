<?php

namespace App\Application\Services;

use App\Domain\Entities\SystemSetting;
use App\Domain\Repositories\SystemSettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    private const SENSITIVE_KEYS = [
        'scanner_api_key',
        'binance_api_key',
        'binance_api_secret',
    ];

    private SystemSettingRepositoryInterface $settingRepository;

    public function __construct(SystemSettingRepositoryInterface $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    public function getSetting(string $key, ?string $default = null): ?string
    {
        if (in_array($key, self::SENSITIVE_KEYS, true)) {
            $setting = $this->settingRepository->findByKey($key);

            return $setting ? $setting->getValue() : $default;
        }

        return Cache::remember("system_setting:{$key}", 3600, function () use ($key, $default) {
            $setting = $this->settingRepository->findByKey($key);
            return $setting ? $setting->getValue() : $default;
        });
    }

    public function setSetting(string $key, ?string $value): void
    {
        $existing = $this->settingRepository->findByKey($key);
        $description = $existing ? $existing->getDescription() : null;

        $setting = new SystemSetting($key, $value, $description);
        $this->settingRepository->save($setting);

        Cache::forget("system_setting:{$key}");
        Cache::forget("system_settings_all");
    }

    public function getAllSettings(): array
    {
        $list = $this->settingRepository->findAll();
        $map = [];
        foreach ($list as $setting) {
            $map[$setting->getKey()] = $setting->getValue();
        }

        return $map;
    }

    public function saveSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($key, $value);
        }
    }
}
