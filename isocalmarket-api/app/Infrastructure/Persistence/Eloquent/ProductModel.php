<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['category_id', 'name', 'slug', 'description', 'price', 'status', 'attributes'])]
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
            'attributes' => 'array',
        ];
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItemModel::class, 'product_id');
    }
}
