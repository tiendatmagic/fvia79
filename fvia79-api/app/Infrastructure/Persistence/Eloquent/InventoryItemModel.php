<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

#[Fillable(['batch_id', 'product_id', 'secret_data', 'normalized_secret_data', 'status', 'order_id', 'sold_at'])]
class InventoryItemModel extends Model
{
    use HasUuids, NormalizeTextTrait;

    protected $table = 'inventory_items';

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->isDirty('secret_data')) {
                $plainText = $model->secret_data;
                $parts = explode('|', $plainText);
                $identity = trim($parts[0] ?? '');
                $model->normalized_secret_data = substr(self::normalizeText($identity), 0, 255);
            }
        });
    }

    public function getSecretDataAttribute($value): string
    {
        if (empty($value)) {
            return '';
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function setSecretDataAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['secret_data'] = '';
            return;
        }

        try {
            Crypt::decryptString($value);
            $this->attributes['secret_data'] = (string) $value;
        } catch (\Throwable $e) {
            $this->attributes['secret_data'] = Crypt::encryptString((string) $value);
        }
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatchModel::class, 'batch_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
