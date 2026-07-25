<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('total_deposited', 16, 4)->default(0.0000)->after('balance');
            $table->uuid('membership_tier_id')->nullable()->after('total_deposited');

            $table->foreign('membership_tier_id')
                ->references('id')
                ->on('membership_tiers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['membership_tier_id']);
            $table->dropColumn(['total_deposited', 'membership_tier_id']);
        });
    }
};
