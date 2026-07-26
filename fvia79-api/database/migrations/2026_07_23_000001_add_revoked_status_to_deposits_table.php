<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE deposits MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'pending_payment'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE deposits MODIFY COLUMN status ENUM('pending_payment', 'completed', 'failed', 'expired') NOT NULL DEFAULT 'pending_payment'");
        }
    }
};
