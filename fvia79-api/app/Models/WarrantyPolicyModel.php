<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyPolicyModel extends Model
{
    use HasFactory;

    protected $table = 'warranty_policies';

    protected $fillable = [
        'category',
        'type',
        'content_vi',
        'content_en',
        'sub_content_vi',
        'sub_content_en',
        'tag_vi',
        'tag_en',
        'tag_color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
