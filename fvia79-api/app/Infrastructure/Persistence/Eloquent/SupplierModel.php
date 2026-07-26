<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'code', 'name', 'base_url', 'catalog_driver', 'api_key', 'enabled', 'products_enabled',
    'import_out_of_stock', 'auto_sync', 'auto_translate', 'target_language',
    'sync_interval_minutes', 'price_adjustment_type', 'price_adjustment_value', 'round_vnd_prices',
    'last_synced_at', 'last_sync_status', 'last_sync_error',
])]
class SupplierModel extends Model
{
    use HasUuids;

    protected $table = 'suppliers';

    protected $hidden = ['api_key'];

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'enabled' => 'boolean',
            'products_enabled' => 'boolean',
            'import_out_of_stock' => 'boolean',
            'auto_sync' => 'boolean',
            'auto_translate' => 'boolean',
            'round_vnd_prices' => 'boolean',
            'sync_interval_minutes' => 'integer',
            'price_adjustment_value' => 'float',
            'last_synced_at' => 'datetime',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'supplier_id');
    }
}
