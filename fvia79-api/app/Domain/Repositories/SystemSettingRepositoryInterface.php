<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\SystemSetting;

interface SystemSettingRepositoryInterface
{
    public function findByKey(string $key): ?SystemSetting;
    
    public function save(SystemSetting $setting): SystemSetting;
    
    public function findAll(): array;
}
