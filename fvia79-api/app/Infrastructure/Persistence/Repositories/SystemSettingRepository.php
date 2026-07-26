<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\SystemSetting;
use App\Domain\Repositories\SystemSettingRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use App\Infrastructure\Persistence\Mappers\SystemSettingMapper;

class SystemSettingRepository implements SystemSettingRepositoryInterface
{
    public function findByKey(string $key): ?SystemSetting
    {
        $model = SystemSettingModel::find($key);
        if (!$model) {
            return null;
        }
        return SystemSettingMapper::toDomain($model);
    }

    public function save(SystemSetting $setting): SystemSetting
    {
        $model = SystemSettingMapper::toPersistence($setting);
        $model->save();
        return SystemSettingMapper::toDomain($model);
    }

    public function findAll(): array
    {
        $models = SystemSettingModel::all();
        $settings = [];
        foreach ($models as $model) {
            $settings[] = SystemSettingMapper::toDomain($model);
        }
        return $settings;
    }
}
