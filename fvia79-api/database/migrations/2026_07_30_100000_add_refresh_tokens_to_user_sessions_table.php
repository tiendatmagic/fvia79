<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->char('refresh_token_hash', 64)->nullable()->unique()->after('device_name');
            $table->timestamp('refresh_token_expired_at')->nullable()->index()->after('refresh_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->dropUnique(['refresh_token_hash']);
            $table->dropIndex(['refresh_token_expired_at']);
            $table->dropColumn(['refresh_token_hash', 'refresh_token_expired_at']);
        });
    }
};
