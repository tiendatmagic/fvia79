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
        DB::table('system_settings')
            ->where('key', 'admin_binance_qr')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->insert([
            'key' => 'admin_binance_qr',
            'value' => '',
            'description' => 'Đường dẫn ảnh QR nhận tiền nạp Binance Pay của admin (tĩnh)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
