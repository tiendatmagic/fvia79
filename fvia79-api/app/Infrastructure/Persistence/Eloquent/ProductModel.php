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
    'supplier_id', 'external_id', 'category_id', 'name', 'name_vi', 'name_en', 'slug', 'description', 'description_vi', 'description_en',
    'price', 'is_manual_price', 'manual_price', 'manual_price_currency', 'supplier_price_vnd', 'supplier_stock', 'supplier_out_of_stock_hidden',
    'supplier_synced_at', 'status',
    'supplier_previous_status', 'attributes',
])]
class ProductModel extends Model
{
    use HasUuids, NormalizeTextTrait, SoftDeletes;

    protected $table = 'products';

    protected $casts = [
        'is_manual_price' => 'boolean',
        'manual_price' => 'float',
        'manual_price_currency' => 'string',
        'attributes' => 'array',
        'supplier_out_of_stock_hidden' => 'boolean',
        'supplier_synced_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->isDirty('name')) {
                $model->normalized_name = self::normalizeText($model->name);
            }
        });
    }

    public function getLocalizedName(string $lang = 'en'): string
    {
        $lang = strtolower(trim($lang));
        if ($lang === 'vi') {
            return $this->name_vi ?: ($this->name_en ?: ($this->name ?: ''));
        }

        return $this->name_en ?: ($this->name_vi ?: ($this->name ?: ''));
    }

    public function getLocalizedDescription(string $lang = 'en'): ?string
    {
        $lang = strtolower(trim($lang));
        if ($lang === 'vi') {
            return $this->description_vi ?? ($this->description_en ?? $this->description);
        }

        return $this->description_en ?? ($this->description_vi ?? $this->description);
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'is_manual_price' => 'boolean',
            'manual_price' => 'float',
            'manual_price_currency' => 'string',
            'supplier_price_vnd' => 'float',
            'supplier_stock' => 'integer',
            'supplier_out_of_stock_hidden' => 'boolean',
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
