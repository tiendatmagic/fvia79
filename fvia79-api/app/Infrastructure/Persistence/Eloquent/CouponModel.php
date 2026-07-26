<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Str;

#[Fillable(['code', 'type', 'value', 'min_spend', 'start_date', 'end_date', 'usage_limit', 'used_count'])]
class CouponModel extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'coupons';

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'min_spend' => 'float',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }
}
