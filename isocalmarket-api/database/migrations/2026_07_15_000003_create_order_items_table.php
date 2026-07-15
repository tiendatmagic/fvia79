<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('restrict');
            $table->string('product_name'); // Lưu tên sản phẩm tại thời điểm đặt hàng (snapshot)
            $table->decimal('price', 15, 2);
            $table->integer('quantity');
            $table->timestamps();

            // Index tối ưu truy vấn theo đơn hàng và sản phẩm
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
