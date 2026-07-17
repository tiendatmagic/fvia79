<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->text('data'); // Lưu trữ thông tin tài khoản, khóa... dưới dạng mã hóa
            $table->enum('status', ['available', 'reserved', 'sold'])->default('available');
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
