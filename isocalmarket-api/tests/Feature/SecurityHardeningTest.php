<?php

namespace Tests\Feature;

use App\Application\Services\AuthService;
use App\Application\Services\SystemSettingService;
use App\Http\Middleware\VerifyCookieRequestOrigin;
use App\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_cookie_authenticated_unsafe_request_requires_trusted_origin_and_ajax_header(): void
    {
        $middleware = app(VerifyCookieRequestOrigin::class);

        $missingHeaders = Request::create('/api/v1/orders', 'POST', cookies: [
            'access_token' => 'signed-token',
        ]);
        $missingResponse = $middleware->handle(
            $missingHeaders,
            fn () => response()->json(['ok' => true])
        );
        $this->assertSame(419, $missingResponse->getStatusCode());

        $untrustedOrigin = Request::create('/api/v1/orders', 'POST', server: [
            'HTTP_ORIGIN' => 'https://attacker.example',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ], cookies: ['access_token' => 'signed-token']);
        $untrustedResponse = $middleware->handle(
            $untrustedOrigin,
            fn () => response()->json(['ok' => true])
        );
        $this->assertSame(419, $untrustedResponse->getStatusCode());

        $trustedRequest = Request::create('/api/v1/orders', 'POST', server: [
            'HTTP_ORIGIN' => 'https://isocalmarket.netlify.app',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ], cookies: ['access_token' => 'signed-token']);
        $trustedResponse = $middleware->handle(
            $trustedRequest,
            fn () => response()->json(['ok' => true])
        );
        $this->assertSame(200, $trustedResponse->getStatusCode());
    }

    public function test_force_logout_revokes_jwt_without_changing_application_key(): void
    {
        $admin = $this->createAdmin();
        $token = app(AuthService::class)->generateAccessToken(UserMapper::toDomain($admin));
        $applicationKey = config('app.key');

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk();

        Artisan::call('auth:force-logout-all');

        $this->assertSame($applicationKey, config('app.key'));
        $this->assertSame(1, (int) $admin->fresh()->token_version);
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_sensitive_settings_are_transparently_encrypted_at_rest(): void
    {
        SystemSettingModel::updateOrCreate(
            ['key' => 'scanner_api_key'],
            ['value' => 'existing-secret', 'description' => 'Test secret']
        );

        $encryptedValue = DB::table('system_settings')
            ->where('key', 'scanner_api_key')
            ->value('value');

        $this->assertNotSame('existing-secret', $encryptedValue);
        $this->assertSame('existing-secret', Crypt::decryptString($encryptedValue));
        $this->assertSame('existing-secret', SystemSettingModel::findOrFail('scanner_api_key')->value);
    }

    public function test_sensitive_settings_bypass_cache_and_are_masked_in_admin_response(): void
    {
        $admin = $this->createAdmin();
        SystemSettingModel::updateOrCreate(
            ['key' => 'scanner_api_key'],
            ['value' => 'real-scanner-secret', 'description' => 'Test secret']
        );
        SystemSettingModel::updateOrCreate(
            ['key' => 'binance_api_key'],
            ['value' => 'real-binance-key', 'description' => 'Test secret']
        );

        Cache::put('system_setting:scanner_api_key', 'poisoned-cache-value', 3600);
        $this->assertSame(
            'real-scanner-secret',
            app(SystemSettingService::class)->getSetting('scanner_api_key')
        );

        $token = app(AuthService::class)->generateAccessToken(UserMapper::toDomain($admin));
        $response = $this->withToken($token)->getJson('/api/v1/admin/settings');

        $response->assertOk()
            ->assertJsonMissingPath('scanner_api_key')
            ->assertJsonMissingPath('binance_api_key')
            ->assertJsonMissingPath('binance_api_secret')
            ->assertJsonPath('has_scanner_api_key', true)
            ->assertJsonPath('has_binance_api_key', true)
            ->assertJsonPath('has_binance_api_secret', false);
    }

    public function test_password_change_revokes_old_token_and_rotates_current_session_tokens(): void
    {
        $user = UserModel::create([
            'username' => 'password_change_user',
            'name' => 'Password Change User',
            'email' => 'password-change@example.com',
            'password' => 'old-password-123',
            'role' => 'customer',
            'balance' => 0,
            'google2fa_enabled' => false,
            'status' => 'active',
            'refresh_token_hash' => hash('sha256', 'existing-refresh-token'),
            'refresh_token_expired_at' => now()->addDay(),
        ]);
        $token = app(AuthService::class)->generateAccessToken(UserMapper::toDomain($user));

        $response = $this->withToken($token)->putJson('/api/v1/profile/password', [
            'current_password' => 'old-password-123',
            'new_password' => 'new-password-456',
            'new_password_confirmation' => 'new-password-456',
        ]);

        $response->assertOk()
            ->assertCookie('access_token')
            ->assertCookie('refresh_token');

        $user->refresh();
        $this->assertSame(1, $user->token_version);
        $this->assertNotNull($user->refresh_token_hash);
        $this->assertNotSame(hash('sha256', 'existing-refresh-token'), $user->refresh_token_hash);
        $this->assertNotNull($user->refresh_token_expired_at);
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_api_key_is_encrypted_in_db_and_can_be_decrypted(): void
    {
        $user = UserModel::create([
            'username' => 'api_key_test_user',
            'name' => 'API Key Test',
            'email' => 'api-key-test@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'balance' => 0,
            'google2fa_enabled' => false,
            'status' => 'active',
            'api_key' => 'MY-SECRET-B2B-KEY-32-CHARS-12345'
        ]);

        $dbRaw = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotSame('MY-SECRET-B2B-KEY-32-CHARS-12345', $dbRaw->api_key);
        
        $this->assertSame(hash('sha256', 'MY-SECRET-B2B-KEY-32-CHARS-12345'), $dbRaw->api_key_hash);

        $userFresh = UserModel::find($user->id);
        $this->assertSame('MY-SECRET-B2B-KEY-32-CHARS-12345', $userFresh->api_key);
    }

    public function test_api_key_backfill_migration_repairs_legacy_plaintext_rows_idempotently(): void
    {
        $user = UserModel::create([
            'username' => 'legacy_api_key_user',
            'name' => 'Legacy API Key User',
            'email' => 'legacy-api-key@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'balance' => 0,
            'status' => 'active',
        ]);
        $plainApiKey = 'LEGACY-PLAINTEXT-API-KEY';

        DB::table('users')->where('id', $user->id)->update([
            'api_key' => $plainApiKey,
            'api_key_hash' => null,
        ]);

        $migration = require database_path('migrations/2026_07_21_220000_backfill_user_api_key_hashes.php');
        $migration->up();
        $migration->up();

        $storedUser = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotSame($plainApiKey, $storedUser->api_key);
        $this->assertSame($plainApiKey, Crypt::decryptString($storedUser->api_key));
        $this->assertSame(hash('sha256', $plainApiKey), $storedUser->api_key_hash);
        $this->assertSame($plainApiKey, UserModel::findOrFail($user->id)->api_key);
    }

    public function test_jwt_routes_reject_api_keys_in_every_supported_b2b_transport(): void
    {
        $user = UserModel::create([
            'username' => 'api_key_fallback_user',
            'name' => 'API Key Fallback User',
            'email' => 'api-key-fallback@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'balance' => 100,
            'google2fa_enabled' => false,
            'status' => 'active',
            'api_key' => 'MY-FALLBACK-B2B-KEY-12345'
        ]);

        $response1 = $this->withHeaders([
            'X-API-KEY' => 'MY-FALLBACK-B2B-KEY-12345'
        ])->getJson('/api/v1/auth/me');

        $response1->assertUnauthorized();

        $response2 = $this->getJson('/api/v1/auth/me?api_key=MY-FALLBACK-B2B-KEY-12345');
        $response2->assertUnauthorized();

        $response3 = $this->withHeaders([
            'Authorization' => 'MY-FALLBACK-B2B-KEY-12345'
        ])->getJson('/api/v1/auth/me');
        $response3->assertUnauthorized();

        $this->withHeaders([
            'X-API-KEY' => 'MY-FALLBACK-B2B-KEY-12345'
        ])->getJson('/api/v1/admin/settings')->assertUnauthorized();
    }

    public function test_banned_user_is_completely_blocked_across_all_auth_transports_and_endpoints(): void
    {
        $bannedUser = UserModel::create([
            'username' => 'banned_test_user',
            'name' => 'Banned Test User',
            'email' => 'banned-test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'admin',
            'balance' => 500,
            'google2fa_enabled' => false,
            'status' => 'banned',
            'api_key' => 'BANNED-USER-B2B-KEY-123456',
            'refresh_token_hash' => hash('sha256', 'banned-refresh-token'),
            'refresh_token_expired_at' => now()->addDays(7),
        ]);

        // 1. Normal Login is blocked (403)
        $this->postJson('/api/v1/auth/login', [
            'email' => 'banned-test@example.com',
            'password' => 'password123',
        ])->assertStatus(403);

        // 2. Token refresh is blocked (401)
        $this->withUnencryptedCookie('refresh_token', 'banned-refresh-token')
            ->postJson('/api/v1/auth/refresh')
            ->assertUnauthorized();

        // 3. JWT Access Token is blocked (403)
        $accessToken = app(AuthService::class)->generateAccessToken(UserMapper::toDomain($bannedUser));
        $this->withToken($accessToken)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(403);

        // 4. B2B API Key is blocked (403)
        $this->withHeaders(['X-API-KEY' => 'BANNED-USER-B2B-KEY-123456'])
            ->getJson('/api/v1/b2b/products')
            ->assertStatus(403);

        // 5. Admin API Key is blocked (403)
        $this->withHeaders(['X-API-KEY' => 'BANNED-USER-B2B-KEY-123456'])
            ->getJson('/api/v1/admin/b2b/stats')
            ->assertStatus(403);

        // 6. Admin JWT route is blocked (403)
        $this->withToken($accessToken)
            ->getJson('/api/v1/admin/settings')
            ->assertStatus(403);

        // 7. Banning an active user clears refresh token and increments token_version
        $activeAdmin = $this->createAdmin();
        $adminToken = app(AuthService::class)->generateAccessToken(UserMapper::toDomain($activeAdmin));
        $this->withToken($adminToken)->getJson('/api/v1/admin/settings')->assertOk();

        $updateResp = $this->withToken($adminToken)->putJson('/api/v1/admin/users/' . $activeAdmin->id, [
            'username' => $activeAdmin->username,
            'email' => $activeAdmin->email,
            'role' => $activeAdmin->role,
            'status' => 'banned',
        ]);
        $updateResp->assertOk();

        $freshActiveAdmin = $activeAdmin->fresh();
        $this->assertSame('banned', $freshActiveAdmin->status);
        $this->assertSame(1, (int) $freshActiveAdmin->token_version);
        $this->assertNull($freshActiveAdmin->refresh_token_hash);

        // Subsequent requests with old token fail with 403
        $this->withToken($adminToken)->getJson('/api/v1/admin/settings')->assertStatus(403);
    }

    private function createAdmin(): UserModel
    {
        return UserModel::create([
            'username' => 'security_admin',
            'name' => 'Security Admin',
            'email' => 'security-admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'balance' => 0,
            'google2fa_enabled' => false,
            'status' => 'active',
        ]);
    }
}
