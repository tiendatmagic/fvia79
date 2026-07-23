<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['product_id', 'batch_name', 'initial_count', 'sold_count', 'status'])]
class InventoryBatchModel extends Model
{
    use HasUuids, NormalizeTextTrait;

    protected $table = 'inventory_batches';

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->isDirty('batch_name')) {
                $model->normalized_batch_name = self::normalizeText($model->batch_name);
            }
        });
    }

    public function getKeysPoolAttribute(): string
    {
        if ($this->relationLoaded('items')) {
            return implode("\n", $this->items->pluck('secret_data')->toArray());
        }
        return implode("\n", $this->items()->pluck('secret_data')->toArray());
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItemModel::class, 'batch_id');
    }
}
