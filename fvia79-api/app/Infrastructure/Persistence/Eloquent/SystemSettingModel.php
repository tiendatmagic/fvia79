<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

#[Fillable(['key', 'value', 'description'])]
class SystemSettingModel extends Model
{
    public const SENSITIVE_KEYS = [
        'scanner_api_key',
        'binance_api_key',
        'binance_api_secret',
        'telegram_bot_token',
    ];

    protected $table = 'system_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value !== null && $this->hasSensitiveValue()
                ? Crypt::decryptString($value)
                : $value,
            set: fn (?string $value): ?string => $value !== null && $this->hasSensitiveValue()
                ? Crypt::encryptString($value)
                : $value,
        );
    }

    private function hasSensitiveValue(): bool
    {
        return in_array($this->attributes['key'] ?? null, self::SENSITIVE_KEYS, true);
    }
}
