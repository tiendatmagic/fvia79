<?php

namespace Tests\Feature;

use App\Application\Services\AuthService;
use App\Http\Middleware\VerifyCookieRequestOrigin;
use App\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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

        // 1. Kiểm tra xem trong DB raw có được mã hóa (không bằng plaintext) hay không
        $dbRaw = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotSame('MY-SECRET-B2B-KEY-32-CHARS-12345', $dbRaw->api_key);
        
        // Cột api_key_hash phải lưu mã băm SHA-256
        $this->assertSame(hash('sha256', 'MY-SECRET-B2B-KEY-32-CHARS-12345'), $dbRaw->api_key_hash);

        // 2. Eloquent phải tự động giải mã ra bản rõ khi truy xuất
        $userFresh = UserModel::find($user->id);
        $this->assertSame('MY-SECRET-B2B-KEY-32-CHARS-12345', $userFresh->api_key);
    }

    public function test_jwt_auth_middleware_accepts_api_key_as_fallback(): void
    {
        $user = UserModel::create([
            'username' => 'api_key_fallback_user',
            'name' => 'API Key Fallback User',
            'email' => 'api-key-fallback@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'balance' => 100,
            'google2fa_enabled' => false,
            'status' => 'active',
            'api_key' => 'MY-FALLBACK-B2B-KEY-12345'
        ]);

        // Gọi API /auth/me (bảo vệ bởi jwt.auth) bằng header X-API-KEY
        $response1 = $this->withHeaders([
            'X-API-KEY' => 'MY-FALLBACK-B2B-KEY-12345'
        ])->getJson('/api/v1/auth/me');

        $response1->assertOk();
        $response1->assertJsonPath('user.email', 'api-key-fallback@example.com');

        // Gọi API /auth/me bằng query string api_key
        $response2 = $this->getJson('/api/v1/auth/me?api_key=MY-FALLBACK-B2B-KEY-12345');
        $response2->assertOk();
        $response2->assertJsonPath('user.email', 'api-key-fallback@example.com');

        // Gọi API /auth/me bằng Authorization header thô
        $response3 = $this->withHeaders([
            'Authorization' => 'MY-FALLBACK-B2B-KEY-12345'
        ])->getJson('/api/v1/auth/me');
        $response3->assertOk();
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
