<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_manual_price')->default(false)->after('price');
            $table->decimal('manual_price', 16, 8)->nullable()->after('is_manual_price');
            $table->string('manual_price_currency', 10)->default('VND')->after('manual_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_manual_price', 'manual_price', 'manual_price_currency']);
        });
    }
};
