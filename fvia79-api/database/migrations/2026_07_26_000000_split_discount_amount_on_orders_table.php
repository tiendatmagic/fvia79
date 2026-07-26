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

        // Backfill: orders with coupon_id get their discount_amount copied to coupon_discount_amount,
        // orders without coupon_id get it copied to tier_discount_amount (best-effort).
        // For existing mixed orders, the full amount goes to coupon_discount_amount since
        // we cannot retroactively determine the split.
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
