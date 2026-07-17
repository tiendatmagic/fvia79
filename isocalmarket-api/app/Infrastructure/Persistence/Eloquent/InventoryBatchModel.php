<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['product_id', 'batch_name', 'keys_pool', 'initial_count', 'sold_count', 'status'])]
class InventoryBatchModel extends Model
{
    use HasUuids;

    protected $table = 'inventory_batches';

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
