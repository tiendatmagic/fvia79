<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'supplier_id', 'external_id', 'category_id', 'name', 'slug', 'description',
    'price', 'supplier_price_vnd', 'supplier_stock', 'supplier_synced_at', 'status', 'attributes',
])]
class ProductModel extends Model
{
    use HasUuids, NormalizeTextTrait, SoftDeletes;

    protected $table = 'products';

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->isDirty('name')) {
                $model->normalized_name = self::normalizeText($model->name);
            }
        });
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'supplier_price_vnd' => 'float',
            'supplier_stock' => 'integer',
            'supplier_synced_at' => 'datetime',
            'attributes' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItemModel::class, 'product_id');
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(InventoryBatchModel::class, 'product_id')->where('status', 'active');
    }
}
