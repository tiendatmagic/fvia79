<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('google2fa_secret')->nullable()->after('remember_token');
            $table->boolean('google2fa_enabled')->default(false)->after('google2fa_secret');
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'google2fa_secret', 'google2fa_enabled']);
        });
    }
};
