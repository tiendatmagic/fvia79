<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['level', 'name_vi', 'name_en', 'min_deposit', 'discount_percent', 'icon', 'color', 'is_active'])]
class MembershipTierModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'membership_tiers';

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'min_deposit' => 'float',
            'discount_percent' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(UserModel::class, 'membership_tier_id');
    }
}
