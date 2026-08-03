<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('suppliers')->where('code', 'muabm365')->exists()) {
            DB::table('suppliers')->insert([
                'id' => (string) Str::uuid7(),
                'code' => 'muabm365',
                'name' => 'MUABM365',
                'base_url' => 'https://muabm365.top',
                'catalog_driver' => 'muabm365',
                'enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('suppliers')->where('code', 'muabm365')->delete();
    }
};
