<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSettingTest extends TestCase
{
    use RefreshDatabase;

    private function createTokenForUser(UserModel $user): string
    {
        $domainUser = \App\Infrastructure\Persistence\Mappers\UserMapper::toDomain($user);
        $authService = app(\App\Application\Services\AuthService::class);
        $session = $authService->createSession($domainUser, '127.0.0.1', 'TestAgent');
        return $authService->generateAccessToken($domainUser, $session->id);
    }

    public function test_get_public_settings_returns_defaults()
    {
        $response = $this->getJson('/api/v1/settings/public');

        $response->assertStatus(200)
            ->assertJson([
                'announcement_enabled' => true,
                'announcement_text' => 'Hệ thống tự động cập nhật sản phẩm liên tục 24/7. Chúc quý khách mua sắm vui vẻ!',
                'announcement_effect' => 'marquee',
                'telegram_bot_username' => 'isocalmarketnoti_bot',
            ]);
    }

    public function test_admin_can_update_announcement_settings()
    {
        $admin = UserModel::create([
            'username' => 'testadmin',
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
            'google2fa_enabled' => false,
        ]);

        $token = $this->createTokenForUser($admin);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson('/api/v1/admin/settings', [
                'announcement_enabled' => true,
                'announcement_text' => 'Thông báo bảo trì hệ thống 2h sáng mai!',
                'announcement_effect' => 'bounce',
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'System settings updated successfully.']);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'announcement_enabled',
            'value' => '1',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'announcement_text',
            'value' => 'Thông báo bảo trì hệ thống 2h sáng mai!',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'announcement_effect',
            'value' => 'bounce',
        ]);

        // Check public endpoint returns updated values
        $publicRes = $this->getJson('/api/v1/settings/public');
        $publicRes->assertStatus(200)
            ->assertJson([
                'announcement_enabled' => true,
                'announcement_text' => 'Thông báo bảo trì hệ thống 2h sáng mai!',
                'announcement_effect' => 'bounce',
            ]);
    }
}
