<?php

namespace Tests\Feature;

use App\Application\Services\AuthService;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\UserSessionModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_creates_user_session(): void
    {
        $user = UserModel::create([
            'username' => 'sessionuser1',
            'name' => 'Session User 1',
            'email' => 'sessiontest1@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'google2fa_enabled' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'sessiontest1@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_sessions', [
            'user_id' => $user->id,
        ]);
    }

    public function test_get_sessions_returns_paginated_list_with_is_current_flag(): void
    {
        $user = UserModel::create([
            'username' => 'sessionuser2',
            'name' => 'Session User 2',
            'email' => 'sessiontest2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'google2fa_enabled' => false,
        ]);

        $domainUser = UserMapper::toDomain($user);
        $authService = app(AuthService::class);
        $session = $authService->createSession($domainUser, '127.0.0.1', 'Mozilla/5.0 Chrome/120.0');
        $token = $authService->generateAccessToken($domainUser, $session->id);

        $response = $this->withToken($token)->getJson('/api/v1/user/sessions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'ip_address', 'device_name', 'last_activity_at', 'is_current'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertTrue($data[0]['is_current']);
    }

    public function test_user_can_revoke_other_session_but_not_current_session(): void
    {
        $user = UserModel::create([
            'username' => 'sessionuser3',
            'name' => 'Session User 3',
            'email' => 'sessiontest3@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'google2fa_enabled' => false,
        ]);

        $domainUser = UserMapper::toDomain($user);
        $authService = app(AuthService::class);
        $currentSession = $authService->createSession($domainUser, '127.0.0.1', 'Mozilla/5.0 Chrome/120.0');
        $token = $authService->generateAccessToken($domainUser, $currentSession->id);

        // Create a secondary session for another device
        $otherSession = UserSessionModel::create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)',
            'device_name' => 'Apple Safari (iPhone)',
            'last_activity_at' => now(),
        ]);

        // Attempting to revoke current session should fail (400)
        $revokeCurrentResponse = $this->withToken($token)
            ->deleteJson("/api/v1/user/sessions/{$currentSession->id}");
        $revokeCurrentResponse->assertStatus(400);

        // Revoking other session should succeed (200)
        $revokeOtherResponse = $this->withToken($token)
            ->deleteJson("/api/v1/user/sessions/{$otherSession->id}");
        $revokeOtherResponse->assertStatus(200);

        $this->assertDatabaseMissing('user_sessions', [
            'id' => $otherSession->id,
        ]);
    }

    public function test_refresh_token_fails_if_session_was_revoked(): void
    {
        $user = UserModel::create([
            'username' => 'sessionuser4',
            'name' => 'Session User 4',
            'email' => 'sessiontest4@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'google2fa_enabled' => false,
        ]);

        $domainUser = UserMapper::toDomain($user);
        $authService = app(AuthService::class);
        $session = $authService->createSession($domainUser, '127.0.0.1', 'Mozilla/5.0 Chrome/120.0');
        $accessToken = $authService->generateAccessToken($domainUser, $session->id);
        $refreshToken = $authService->generateRefreshToken($domainUser);

        // Delete the session to simulate remote logout
        $session->delete();

        // Send refresh request with expired/existing access token cookie and valid refresh token cookie
        $refreshResponse = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $refreshResponse->assertStatus(401);
    }

    public function test_user_can_revoke_all_other_sessions(): void
    {
        $user = UserModel::create([
            'username' => 'sessionuser5',
            'name' => 'Session User 5',
            'email' => 'sessiontest5@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'google2fa_enabled' => false,
        ]);

        $domainUser = UserMapper::toDomain($user);
        $authService = app(AuthService::class);
        $currentSession = $authService->createSession($domainUser, '127.0.0.1', 'Mozilla/5.0 Chrome/120.0');
        $token = $authService->generateAccessToken($domainUser, $currentSession->id);

        UserSessionModel::create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.101',
            'user_agent' => 'Firefox',
            'device_name' => 'Firefox (Windows)',
            'last_activity_at' => now(),
        ]);
        UserSessionModel::create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.102',
            'user_agent' => 'Safari',
            'device_name' => 'Safari (Mac)',
            'last_activity_at' => now(),
        ]);

        $response = $this->withToken($token)->deleteJson('/api/v1/user/sessions/others');
        $response->assertStatus(200);

        $this->assertDatabaseHas('user_sessions', ['id' => $currentSession->id]);
        $this->assertEquals(1, UserSessionModel::where('user_id', $user->id)->count());
    }
}
