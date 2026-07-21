<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumns('users', ['api_key', 'api_key_hash'])) {
            return;
        }

        DB::transaction(function (): void {
            $users = DB::table('users')
                ->whereNotNull('api_key')
                ->where('api_key', '!=', '')
                ->get(['id', 'api_key', 'api_key_hash']);

            foreach ($users as $user) {
                $storedValue = (string) $user->api_key;
                $updates = [];

                try {
                    $plainApiKey = Crypt::decryptString($storedValue);
                } catch (DecryptException $exception) {
                    if ($this->looksLikeEncryptedPayload($storedValue)) {
                        throw new RuntimeException(
                            "Unable to decrypt API key for user {$user->id}. Verify APP_KEY before migrating.",
                            previous: $exception,
                        );
                    }

                    $plainApiKey = $storedValue;
                    $updates['api_key'] = Crypt::encryptString($plainApiKey);
                }

                $expectedHash = hash('sha256', $plainApiKey);
                if (! hash_equals($expectedHash, (string) ($user->api_key_hash ?? ''))) {
                    $updates['api_key_hash'] = $expectedHash;
                }

                if ($updates !== []) {
                    DB::table('users')->where('id', $user->id)->update($updates);
                }
            }
        });
    }

    public function down(): void
    {
        // Data repair is intentionally not reversed; clearing hashes would break B2B authentication.
    }

    private function looksLikeEncryptedPayload(string $value): bool
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload)
            && isset($payload['iv'], $payload['value'], $payload['mac']);
    }
};
