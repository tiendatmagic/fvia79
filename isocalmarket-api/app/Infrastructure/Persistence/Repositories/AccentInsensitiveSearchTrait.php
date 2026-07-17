<?php

namespace App\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Facades\DB;

trait AccentInsensitiveSearchTrait
{
    protected function applyAccentInsensitiveSearch($query, string $column, string $search)
    {
        $normalizedSearch = mb_strtolower($search, 'UTF-8');
        $normalizedSearch = str_replace(['đ', 'Đ'], 'd', $normalizedSearch);

        $query->where(DB::raw("REPLACE(REPLACE(LOWER({$column}), 'đ', 'd'), 'Đ', 'd')"), 'like', "%{$normalizedSearch}%");
    }

    protected function applyAccentInsensitiveOrSearch($query, string $column, string $search)
    {
        $normalizedSearch = mb_strtolower($search, 'UTF-8');
        $normalizedSearch = str_replace(['đ', 'Đ'], 'd', $normalizedSearch);

        $query->orWhere(DB::raw("REPLACE(REPLACE(LOWER({$column}), 'đ', 'd'), 'Đ', 'd')"), 'like', "%{$normalizedSearch}%");
    }
}
