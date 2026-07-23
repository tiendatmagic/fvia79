<?php

namespace App\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Facades\DB;

trait AccentInsensitiveSearchTrait
{
    protected function applyAccentInsensitiveSearch($query, string $column, string $search)
    {
        $normalizedSearch = $this->normalizeSearchKeyword($search);
        $normalizedColumn = $this->getNormalizedColumnName($column);
        $query->where($normalizedColumn, 'like', "%{$normalizedSearch}%");
    }

    protected function applyAccentInsensitiveOrSearch($query, string $column, string $search)
    {
        $normalizedSearch = $this->normalizeSearchKeyword($search);
        $normalizedColumn = $this->getNormalizedColumnName($column);
        $query->orWhere($normalizedColumn, 'like', "%{$normalizedSearch}%");
    }

    private function getNormalizedColumnName(string $column): string
    {
        if ($column === 'name') {
            return 'normalized_name';
        }
        if ($column === 'batch_name') {
            return 'normalized_batch_name';
        }
        if ($column === 'secret_data') {
            return 'normalized_secret_data';
        }
        return $column;
    }

    private function normalizeSearchKeyword(string $search): string
    {
        if (empty($search)) return '';
        
        if (class_exists('\Normalizer')) {
            $search = \Normalizer::normalize($search, \Normalizer::FORM_C);
        }
        
        $text = mb_strtolower($search, 'UTF-8');

        $accentMap = [
            'a' => ['á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ'],
            'e' => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ'],
            'i' => ['í', 'ì', 'ỉ', 'ĩ', 'ị'],
            'o' => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ'],
            'u' => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'],
            'y' => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'],
            'd' => ['đ']
        ];

        foreach ($accentMap as $replace => $searchChar) {
            $text = str_replace($searchChar, $replace, $text);
        }

        return $text;
    }
}
