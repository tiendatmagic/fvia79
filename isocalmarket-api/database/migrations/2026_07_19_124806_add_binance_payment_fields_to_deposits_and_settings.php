<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('payment_method')->default('bep20')->after('status');
            $table->string('payer_binance_id')->nullable()->after('payment_method');
            $table->string('admin_binance_id')->nullable()->after('payer_binance_id');

            $table->index(['payer_binance_id', 'payment_method']);
        });

        // Insert system settings
        DB::table('system_settings')->insert([
            [
                'key' => 'admin_binance_id',
                'value' => '',
                'description' => 'Binance ID nhận tiền nạp của admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'admin_binance_qr',
                'value' => '',
                'description' => 'Đường dẫn ảnh QR nhận tiền nạp Binance Pay của admin (tĩnh)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'binance_api_key',
                'value' => '',
                'description' => 'Binance API Key của admin để đối soát nạp tiền qua Binance ID',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'binance_api_secret',
                'value' => '',
                'description' => 'Binance API Secret của admin để đối soát nạp tiền qua Binance ID',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropIndex(['payer_binance_id', 'payment_method']);
            $table->dropColumn(['payment_method', 'payer_binance_id', 'admin_binance_id']);
        });

        DB::table('system_settings')
            ->whereIn('key', ['admin_binance_id', 'admin_binance_qr', 'binance_api_key', 'binance_api_secret'])
            ->delete();
    }
};