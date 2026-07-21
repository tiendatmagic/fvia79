<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Thêm cột api_key_hash sau api_key
            $table->string('api_key_hash', 64)->nullable()->unique()->index()->after('api_key');

            // Bỏ unique constraint trên api_key để hỗ trợ lưu trữ chuỗi mã hóa AES-256 an toàn
            $table->dropUnique(['api_key']);
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['api_key']);
            $table->dropColumn('api_key_hash');
        });
    }
};
