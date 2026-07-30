<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('tier_discount_amount', 16, 4)->default(0)->after('discount_amount');
            $table->decimal('coupon_discount_amount', 16, 4)->default(0)->after('tier_discount_amount');
        });

        DB::statement("
            UPDATE orders
            SET coupon_discount_amount = discount_amount
            WHERE coupon_id IS NOT NULL AND discount_amount > 0
        ");
        DB::statement("
            UPDATE orders
            SET tier_discount_amount = discount_amount
            WHERE coupon_id IS NULL AND discount_amount > 0
        ");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tier_discount_amount', 'coupon_discount_amount']);
        });
    }
};
