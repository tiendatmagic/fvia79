<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('deleted_at');
            $table->index('created_at');
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->index('created_at');
        });
    }
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
