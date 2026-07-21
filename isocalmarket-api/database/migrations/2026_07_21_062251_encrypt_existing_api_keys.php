<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('api_key')->nullable()->change();
        });

        $hasApiKeyHash = Schema::hasColumn('users', 'api_key_hash');
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
                $plainKey = $decrypted;
                $hash = hash('sha256', $plainKey);

                if ($hasApiKeyHash && empty($user->api_key_hash)) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['api_key_hash' => $hash]);
                }
            } else {
                $plainKey = $rawApiKey;
                $encryptedKey = Crypt::encryptString($plainKey);
                $hash = hash('sha256', $plainKey);

                $updates = ['api_key' => $encryptedKey];
                if ($hasApiKeyHash) {
                    $updates['api_key_hash'] = $hash;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update($updates);
            }
        }
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('api_key', 255)->nullable()->change();
        });
    }
};
