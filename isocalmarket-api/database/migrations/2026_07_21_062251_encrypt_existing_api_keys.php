<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thay đổi kiểu dữ liệu cột api_key từ string (varchar(255)) sang text để có đủ độ dài chứa chuỗi mã hóa AES-256
        Schema::table('users', function (Blueprint $table) {
            $table->text('api_key')->nullable()->change();
        });

        // 2. Duyệt qua tất cả các user để kiểm tra và mã hóa api_key nếu là plaintext, đồng thời cập nhật api_key_hash
        $users = DB::table('users')->whereNotNull('api_key')->where('api_key', '!=', '')->get();

        foreach ($users as $user) {
            $rawApiKey = $user->api_key;
            $decrypted = null;
            $isAlreadyEncrypted = false;

            try {
                $decrypted = Crypt::decryptString($rawApiKey);
                $isAlreadyEncrypted = true;
            } catch (DecryptException $e) {
                $isAlreadyEncrypted = false;
            }

            if ($isAlreadyEncrypted) {
                // Nếu đã được mã hóa, chỉ cần đảm bảo api_key_hash khớp
                $plainKey = $decrypted;
                $hash = hash('sha256', $plainKey);

                if (empty($user->api_key_hash)) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['api_key_hash' => $hash]);
                }
            } else {
                // Nếu là plaintext, tiến hành mã hóa và băm
                $plainKey = $rawApiKey;
                $encryptedKey = Crypt::encryptString($plainKey);
                $hash = hash('sha256', $plainKey);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'api_key' => $encryptedKey,
                        'api_key_hash' => $hash
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_key', 255)->nullable()->change();
        });
    }
};
