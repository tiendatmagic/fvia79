<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'auto_hide_out_of_stock')) {
                $table->boolean('auto_hide_out_of_stock')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'auto_hide_out_of_stock')) {
                $table->dropColumn('auto_hide_out_of_stock');
            }
        });
    }
};
