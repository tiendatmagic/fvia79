<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['supplier_id', 'external_id', 'name', 'name_vi', 'name_en', 'slug', 'description', 'description_vi', 'description_en', 'is_active'])]
class CategoryModel extends Model
{
    use HasUuids, NormalizeTextTrait;

    protected $table = 'categories';

    protected $casts = [
        'is_active' => 'boolean',
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

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id');
    }
}
