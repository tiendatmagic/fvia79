<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('suppliers')->where('code', 'mediahub')->exists()) {
            DB::table('suppliers')->insert([
                'id' => (string) Str::uuid7(),
                'code' => 'mediahub',
                'name' => 'MEDIAHUB',
                'base_url' => 'https://canboso.com',
                'catalog_driver' => 'mediahub',
                'enabled' => true,
                'products_enabled' => true,
                'import_out_of_stock' => true,
                'auto_sync' => true,
                'auto_translate' => true,
                'sync_interval_minutes' => 30,
                'target_language' => 'vi',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('suppliers')->where('code', 'mediahub')->update([
                'auto_sync' => true,
                'auto_translate' => true,
                'sync_interval_minutes' => 30,
                'target_language' => 'vi',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('suppliers')->where('code', 'mediahub')->delete();
    }
};
