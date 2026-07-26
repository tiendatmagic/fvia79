<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('auto_translate')->default(false)->after('auto_sync');
            $table->string('target_language', 10)->default('vi')->nullable()->after('auto_translate');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['auto_translate', 'target_language']);
        });
    }
};
