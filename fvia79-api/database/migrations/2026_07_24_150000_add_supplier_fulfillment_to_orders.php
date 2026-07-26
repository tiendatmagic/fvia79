<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('idempotency_key')->nullable()->after('order_code');
            $table->decimal('total_price', 20, 8)->change();
            $table->decimal('discount_amount', 20, 8)->default(0)->change();
            $table->unique(['user_id', 'idempotency_key']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('supplier_quantity')->default(0)->after('quantity');
            $table->unsignedInteger('local_quantity')->default(0)->after('supplier_quantity');
            $table->string('supplier_order_id')->nullable()->after('local_quantity');
            $table->string('supplier_code', 50)->nullable()->after('supplier_order_id');
            $table->decimal('price', 20, 8)->change();
            $table->decimal('cost_price', 20, 8)->nullable()->change();
        });

        Schema::create('supplier_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignUuid('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->string('supplier_order_id')->nullable();
            $table->unsignedInteger('quantity');
            $table->string('status', 20)->default('pending');
            $table->longText('response_payload')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
            $table->index('supplier_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_purchases');

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
            $table->decimal('cost_price', 15, 2)->nullable()->change();
            $table->dropColumn([
                'supplier_quantity',
                'local_quantity',
                'supplier_order_id',
                'supplier_code',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'idempotency_key']);
            $table->decimal('total_price', 15, 2)->change();
            $table->decimal('discount_amount', 15, 2)->default(0)->change();
            $table->dropColumn('idempotency_key');
        });
    }
};
