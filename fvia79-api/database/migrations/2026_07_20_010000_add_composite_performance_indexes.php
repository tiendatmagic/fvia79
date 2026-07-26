<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'orders_user_status_created_idx');
            $table->index(['status', 'created_at'], 'orders_status_created_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['category_id', 'status', 'created_at'], 'products_cat_status_created_idx');
            $table->index(['status', 'created_at'], 'products_status_created_idx');
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->index(['product_id', 'status'], 'inv_batches_product_status_idx');
        });
    }
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_status_created_idx');
            $table->dropIndex('orders_status_created_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_cat_status_created_idx');
            $table->dropIndex('products_status_created_idx');
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropIndex('inv_batches_product_status_idx');
        });
    }
};
