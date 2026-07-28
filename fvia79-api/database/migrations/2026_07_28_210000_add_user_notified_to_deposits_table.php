<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('deposits', 'user_notified')) {
            Schema::table('deposits', function (Blueprint $table) {
                $table->boolean('user_notified')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('deposits', 'user_notified')) {
            Schema::table('deposits', function (Blueprint $table) {
                $table->dropColumn('user_notified');
            });
        }
    }
};
