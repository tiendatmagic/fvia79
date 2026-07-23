<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['batch_id', 'product_id', 'secret_data', 'normalized_secret_data', 'status', 'order_id', 'sold_at'])]
class InventoryItemModel extends Model
{
    use HasUuids, NormalizeTextTrait;

    protected $table = 'inventory_items';

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->isDirty('secret_data')) {
                $model->normalized_secret_data = substr(self::normalizeText($model->secret_data), 0, 255);
            }
        });
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
