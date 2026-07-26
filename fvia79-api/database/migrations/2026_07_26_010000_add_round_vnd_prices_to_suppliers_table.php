<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'round_vnd_prices')) {
                $table->boolean('round_vnd_prices')->default(false)->after('price_adjustment_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'round_vnd_prices')) {
                $table->dropColumn('round_vnd_prices');
            }
        });
    }
};
