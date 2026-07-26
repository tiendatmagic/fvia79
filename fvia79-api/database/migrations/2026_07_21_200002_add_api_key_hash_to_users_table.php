<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'api_key_hash')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('api_key_hash', 64)->nullable()->unique()->index()->after('api_key');
            });
        }

        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE users DROP INDEX users_api_key_unique');
        } catch (\Throwable $e) {}
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['api_key']);
            $table->dropColumn('api_key_hash');
        });
    }
};
