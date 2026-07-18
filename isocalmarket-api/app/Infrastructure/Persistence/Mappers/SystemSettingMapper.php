<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\SystemSetting;
use App\Infrastructure\Persistence\Eloquent\SystemSettingModel;

class SystemSettingMapper
{
    public static function toDomain(SystemSettingModel $model): SystemSetting
    {
        return new SystemSetting(
            $model->key,
            $model->value,
            $model->description,
            $model->created_at,
            $model->updated_at
        );
    }

    public static function toPersistence(SystemSetting $entity): SystemSettingModel
    {
        $model = SystemSettingModel::find($entity->getKey()) ?: new SystemSettingModel();
        
        $model->key = $entity->getKey();
        $model->value = $entity->getValue();
        $model->description = $entity->getDescription();

        return $model;
    }
}
