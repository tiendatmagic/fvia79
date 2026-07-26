<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

#[Fillable([
    'order_id', 'product_id', 'product_name', 'price', 'cost_price', 'quantity',
    'supplier_quantity', 'local_quantity', 'supplier_order_id', 'supplier_code', 'delivered_keys',
])]
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
            'cost_price' => 'float',
            'quantity' => 'integer',
            'supplier_quantity' => 'integer',
            'local_quantity' => 'integer',
        ];
    }

    public function getDeliveredKeysAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($value);
            $decoded = json_decode($decrypted, true);

            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }
    }

    public function setDeliveredKeysAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['delivered_keys'] = Crypt::encryptString(json_encode([], JSON_UNESCAPED_UNICODE));

            return;
        }

        $jsonString = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

        try {
            Crypt::decryptString($jsonString);
            $this->attributes['delivered_keys'] = $jsonString;
        } catch (\Throwable $e) {
            $this->attributes['delivered_keys'] = Crypt::encryptString($jsonString);
        }
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
