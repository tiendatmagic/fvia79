<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

#[Fillable([
    'order_id', 'order_item_id', 'supplier_id', 'product_id', 'supplier_order_id',
    'quantity', 'status', 'response_payload', 'error',
])]
class SupplierPurchaseModel extends Model
{
    use HasUuids;

    protected $table = 'supplier_purchases';

    protected $hidden = ['response_payload'];

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function getResponsePayloadAttribute(?string $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $value = Crypt::decryptString($value);
        } catch (\Throwable) {
            // Supports rows written before encryption was enabled.
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function setResponsePayloadAttribute(mixed $value): void
    {
        if ($value === null) {
            $this->attributes['response_payload'] = null;

            return;
        }

        $json = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        $this->attributes['response_payload'] = Crypt::encryptString($json);
    }
}
