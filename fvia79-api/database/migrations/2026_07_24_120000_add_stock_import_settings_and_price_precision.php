<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('import_out_of_stock')->default(true)->after('products_enabled');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('supplier_out_of_stock_hidden')->default(false)->after('supplier_stock');
            $table->decimal('price', 20, 8)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->change();
            $table->dropColumn('supplier_out_of_stock_hidden');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('import_out_of_stock');
        });
    }
};
