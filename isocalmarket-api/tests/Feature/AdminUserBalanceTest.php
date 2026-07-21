<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserBalanceTest extends TestCase
{
    use RefreshDatabase;

    private UserModel $adminUser;
    private UserModel $normalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77a',
            'username' => 'admin_balance_test',
            'name' => 'System Admin',
            'email' => 'admin_balance_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'balance' => 100.0,
        ]);

        $this->normalUser = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a77b',
            'username' => 'user_balance_test',
            'name' => 'Regular User',
            'email' => 'user_balance_test@isocalmarket.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'balance' => 20.0,
        ]);
    }

    public function test_admin_can_subtract_user_balance(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/v1/admin/users/{$this->normalUser->id}/adjust-balance", [
                'type' => 'subtract',
                'amount' => 7.25,
                'reason' => 'Manual correction',
            ]);

        $response->assertOk()
            ->assertJsonPath('user.balance', 12.75);

        $this->assertEquals(12.75, (float) $this->normalUser->fresh()->balance);
    }

    public function test_admin_cannot_subtract_more_than_user_balance(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/v1/admin/users/{$this->normalUser->id}/adjust-balance", [
                'type' => 'subtract',
                'amount' => 20.01,
            ]);

        $response->assertBadRequest();
        $this->assertEquals(20.0, (float) $this->normalUser->fresh()->balance);
    }

    public function test_admin_can_filter_customers_by_inclusive_date_range(): void
    {
        $this->adminUser->forceFill(['created_at' => '2026-07-09 12:00:00'])->save();
        $this->normalUser->forceFill(['created_at' => '2026-07-10 18:30:00'])->save();

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/v1/admin/users?role=customer&start_date=2026-07-10&end_date=2026-07-10');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->normalUser->id)
            ->assertJsonPath('data.0.role', 'customer');
    }

    public function test_admin_can_create_user_without_optional_display_name(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/v1/admin/users', [
                'username' => 'created_without_name',
                'name' => null,
                'email' => 'created_without_name@example.com',
                'phone' => null,
                'role' => 'customer',
                'balance' => 0,
                'password' => 'password123',
            ]);

        $response->assertCreated()
            ->assertJsonPath('user.username', 'created_without_name')
            ->assertJsonPath('user.name', 'created_without_name');

        $this->assertDatabaseHas('users', [
            'username' => 'created_without_name',
            'name' => 'created_without_name',
            'email' => 'created_without_name@example.com',
        ]);
    }

    public function test_admin_can_clear_display_name_when_updating_user(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->putJson("/api/v1/admin/users/{$this->normalUser->id}", [
                'username' => 'renamed_user',
                'name' => null,
                'email' => $this->normalUser->email,
                'phone' => null,
                'role' => 'customer',
                'balance' => 20,
            ]);

        $response->assertOk()
            ->assertJsonPath('user.username', 'renamed_user')
            ->assertJsonPath('user.name', 'renamed_user');

        $this->assertDatabaseHas('users', [
            'id' => $this->normalUser->id,
            'username' => 'renamed_user',
            'name' => 'renamed_user',
        ]);
    }

    private function adminHeaders(): array
    {
        $token = resolve(JwtService::class)->generateToken([
            'sub' => $this->adminUser->id,
            'email' => $this->adminUser->email,
            'role' => $this->adminUser->role,
        ], 1800);

        return ['Authorization' => "Bearer {$token}"];
    }
}
