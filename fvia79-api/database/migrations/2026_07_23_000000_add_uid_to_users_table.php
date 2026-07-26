<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Infrastructure\Persistence\Eloquent\UserModel;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('uid', 32)->nullable()->unique()->after('id');
        });

        $users = UserModel::whereNull('uid')->get();
        foreach ($users as $user) {
            $user->uid = UserModel::generateUniqueUid();
            $user->save();
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
};
