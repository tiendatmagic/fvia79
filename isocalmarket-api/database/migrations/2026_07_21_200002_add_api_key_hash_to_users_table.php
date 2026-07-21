<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_key_hash', 64)->nullable()->unique()->index()->after('api_key');

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
