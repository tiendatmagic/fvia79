<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('normalized_name')->nullable()->index();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('normalized_name')->nullable()->index();
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->string('normalized_batch_name')->nullable()->index();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('normalized_name')->nullable()->index();
        });

        // Backfill data
        \Illuminate\Support\Facades\DB::table('categories')->get()->each(function ($cat) {
            \Illuminate\Support\Facades\DB::table('categories')->where('id', $cat->id)->update([
                'normalized_name' => $this->normalizeText($cat->name)
            ]);
        });

        \Illuminate\Support\Facades\DB::table('products')->get()->each(function ($p) {
            \Illuminate\Support\Facades\DB::table('products')->where('id', $p->id)->update([
                'normalized_name' => $this->normalizeText($p->name)
            ]);
        });

        \Illuminate\Support\Facades\DB::table('inventory_batches')->get()->each(function ($b) {
            \Illuminate\Support\Facades\DB::table('inventory_batches')->where('id', $b->id)->update([
                'normalized_batch_name' => $this->normalizeText($b->batch_name)
            ]);
        });

        \Illuminate\Support\Facades\DB::table('users')->get()->each(function ($u) {
            \Illuminate\Support\Facades\DB::table('users')->where('id', $u->id)->update([
                'normalized_name' => $this->normalizeText($u->name)
            ]);
        });
    }
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('normalized_name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('normalized_name');
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropColumn('normalized_batch_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('normalized_name');
        });
    }

    private function normalizeText(?string $text): string
    {
        if (empty($text)) return '';
        $text = mb_strtolower($text, 'UTF-8');

        $accentMap = [
            'a' => ['á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ'],
            'e' => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ'],
            'i' => ['í', 'ì', 'ỉ', 'ĩ', 'ị'],
            'o' => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ'],
            'u' => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'],
            'y' => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'],
            'd' => ['đ']
        ];

        foreach ($accentMap as $replace => $search) {
            $text = str_replace($search, $replace, $text);
        }

        return $text;
    }
};
