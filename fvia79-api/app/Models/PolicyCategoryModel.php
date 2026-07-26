<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyCategoryModel extends Model
{
    use HasFactory;

    protected $table = 'policy_categories';

    protected $fillable = [
        'key',
        'name_vi',
        'name_en',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
