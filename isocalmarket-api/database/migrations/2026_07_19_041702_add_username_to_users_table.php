<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('id');
        });
 
        // Điền username tự động cho các tài khoản cũ
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            if ($user->name === 'admin') {
                $username = 'admin';
            } else {
                $username = strstr($user->email, '@', true);
                if (!$username) {
                    $username = 'user_' . Str::random(5);
                }
                $username = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
                $original = $username;
                $counter = 1;
                while (DB::table('users')->where('username', $username)->exists()) {
                    $username = $original . $counter;
                    $counter++;
                }
            }
 
            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => strtolower($username)]);
        }
 
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
