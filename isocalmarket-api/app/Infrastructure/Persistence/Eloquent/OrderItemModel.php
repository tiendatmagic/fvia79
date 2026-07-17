<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['order_id', 'product_id', 'product_name', 'price', 'quantity', 'delivered_keys'])]
class OrderItemModel extends Model
{
    use HasUuids;

    protected $table = 'order_items';

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'quantity' => 'integer',
            'delivered_keys' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
