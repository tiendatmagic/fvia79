<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'order_code', 'idempotency_key', 'total_price', 'status', 'coupon_code', 'discount_amount', 'coupon_id'])]
class OrderModel extends Model
{
    use HasUuids;

    protected $table = 'orders';

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected function casts(): array
    {
        return [
            'total_price' => 'float',
            'discount_amount' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(CouponModel::class, 'coupon_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItemModel::class, 'order_id');
    }
}
