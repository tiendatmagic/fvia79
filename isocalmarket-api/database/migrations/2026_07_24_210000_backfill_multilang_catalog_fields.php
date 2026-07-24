<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'products'] as $table) {
            DB::table($table)
                ->where(function ($query) {
                    $query->whereNull('name_en')->orWhere('name_en', '');
                })
                ->update(['name_en' => DB::raw('name')]);

            DB::table($table)
                ->where(function ($query) {
                    $query->whereNull('name_vi')->orWhere('name_vi', '');
                })
                ->update(['name_vi' => DB::raw('name_en')]);

            DB::table($table)
                ->whereNull('description_en')
                ->update(['description_en' => DB::raw('description')]);

            DB::table($table)
                ->whereNull('description_vi')
                ->update(['description_vi' => DB::raw('description_en')]);
        }
    }

    public function down(): void
    {
        // Data backfill is intentionally not reverted.
    }
};
