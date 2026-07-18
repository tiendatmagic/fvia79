<?php

namespace App\Application\Services;

use App\Domain\Entities\SystemSetting;
use App\Domain\Repositories\SystemSettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    private SystemSettingRepositoryInterface $settingRepository;

    public function __construct(SystemSettingRepositoryInterface $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Lấy giá trị cấu hình theo Key, hỗ trợ Cache 1 giờ để tăng tốc độ và giảm tải DB
     */
    public function getSetting(string $key, ?string $default = null): ?string
    {
        return Cache::remember("system_setting:{$key}", 3600, function () use ($key, $default) {
            $setting = $this->settingRepository->findByKey($key);
            return $setting ? $setting->getValue() : $default;
        });
    }

    /**
     * Cập nhật hoặc thêm mới một cấu hình
     */
    public function setSetting(string $key, ?string $value): void
    {
        $existing = $this->settingRepository->findByKey($key);
        $description = $existing ? $existing->getDescription() : null;

        $setting = new SystemSetting($key, $value, $description);
        $this->settingRepository->save($setting);

        Cache::forget("system_setting:{$key}");
        Cache::forget("system_settings_all");
    }

    /**
     * Lấy tất cả cấu hình dưới dạng key-value map
     */
    public function getAllSettings(): array
    {
        return Cache::remember("system_settings_all", 3600, function () {
            $list = $this->settingRepository->findAll();
            $map = [];
            foreach ($list as $setting) {
                $map[$setting->getKey()] = $setting->getValue();
            }
            return $map;
        });
    }

    /**
     * Cập nhật hàng loạt cấu hình hệ thống
     */
    public function saveSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($key, $value);
        }
    }
}
