<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'code', 'amount_usdt', 'amount_to_pay', 'amount_vnd', 'tx_hash', 'admin_wallet_address', 'status', 'payment_method', 'payer_binance_id', 'admin_binance_id'])]
class DepositModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'deposits';

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected function casts(): array
    {
        return [
            'amount_usdt' => 'float',
            'amount_to_pay' => 'float',
            'amount_vnd' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}
