<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileResponseTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserModel::create([
            'username' => 'profile_contract_user',
            'name' => 'Profile User',
            'email' => 'profile-contract@example.com',
            'phone' => '0900000000',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'balance' => 42.5,
        ]);
    }

    public function test_profile_update_returns_complete_user_contract(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/v1/profile', [
                'name' => 'Updated Profile User',
                'phone' => '0911111111',
            ]);

        $response->assertOk()
            ->assertJsonPath('user.id', $this->user->id)
            ->assertJsonPath('user.username', 'profile_contract_user')
            ->assertJsonPath('user.name', 'Updated Profile User')
            ->assertJsonPath('user.email', 'profile-contract@example.com')
            ->assertJsonPath('user.phone', '0911111111')
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonPath('user.google2fa_enabled', false)
            ->assertJsonPath('user.balance', 42.5)
            ->assertJsonPath('user.api_key', null);
    }

    public function test_api_key_generation_preserves_complete_user_contract(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/profile/api-key');

        $response->assertOk()
            ->assertJsonPath('user.username', 'profile_contract_user')
            ->assertJsonPath('user.name', 'Profile User')
            ->assertJsonPath('user.email', 'profile-contract@example.com')
            ->assertJsonPath('user.phone', '0900000000')
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonPath('user.google2fa_enabled', false)
            ->assertJsonPath('user.balance', 42.5)
            ->assertJsonStructure(['user' => ['api_key']]);

        $this->assertNotEmpty($response->json('user.api_key'));
    }

    public function test_two_factor_enable_returns_a_valid_otpauth_url(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/profile/2fa/enable');

        $response->assertOk()
            ->assertJsonStructure(['secret', 'qr_code_url']);

        $secret = $response->json('secret');
        $qrCodeUrl = $response->json('qr_code_url');

        $this->assertMatchesRegularExpression('/^[A-Z2-7]{16}$/', $secret);
        $this->assertStringStartsWith(
            'otpauth://totp/Fvia79:profile-contract%40example.com?',
            $qrCodeUrl
        );
        $this->assertStringContainsString('secret='.rawurlencode($secret), $qrCodeUrl);
        $this->assertStringContainsString('issuer=Fvia79', $qrCodeUrl);

        $this->user->refresh();
        $this->assertSame($secret, $this->user->google2fa_secret);
        $this->assertFalse((bool) $this->user->google2fa_enabled);
    }

    private function authHeaders(): array
    {
        $token = resolve(JwtService::class)->generateToken([
            'sub' => $this->user->id,
            'email' => $this->user->email,
            'role' => $this->user->role,
            'ver' => (int) $this->user->token_version,
        ], 1800);

        return ['Authorization' => "Bearer {$token}"];
    }
}
