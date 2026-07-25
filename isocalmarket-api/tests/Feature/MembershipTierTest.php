<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MembershipTierTest extends TestCase
{
    use RefreshDatabase;

    protected UserModel $adminModel;

    protected UserModel $customerModel;

    protected string $adminToken;

    protected string $customerToken;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.feature_membership_tiers_enabled' => true]);

        // Seed 5 tiers
        $this->seed(\Database\Seeders\MembershipTierSeeder::class);

        $this->adminModel = UserModel::create([
            'id' => (string) Str::uuid7(),
            'uid' => '10000001',
            'username' => 'admin_user',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'balance' => 1000.0,
            'total_deposited' => 0.0,
            'status' => 'active',
        ]);

        $this->customerModel = UserModel::create([
            'id' => (string) Str::uuid7(),
            'uid' => '10000002',
            'username' => 'customer_user',
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'balance' => 0.0,
            'total_deposited' => 0.0,
            'status' => 'active',
        ]);

        $authService = app(\App\Application\Services\AuthService::class);
        $this->adminToken = $authService->generateAccessToken(UserMapper::toDomain($this->adminModel));
        $this->customerToken = $authService->generateAccessToken(UserMapper::toDomain($this->customerModel));
    }

    public function test_public_can_fetch_membership_tiers(): void
    {
        $response = $this->getJson('/api/v1/membership-tiers');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_admin_can_crud_membership_tiers(): void
    {
        // Fetch
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/membership-tiers');
        $response->assertStatus(200);

        // Create
        $createRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/membership-tiers', [
                'level' => 6,
                'name_vi' => 'Siêu Kim Cương',
                'name_en' => 'Super Diamond',
                'min_deposit' => 5000.0,
                'discount_percent' => 15.0,
                'icon' => 'crown',
                'color' => '#3b82f6',
                'is_active' => true,
            ]);

        $createRes->assertStatus(201);
        $tierId = $createRes->json('data.id');

        // Update
        $updateRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/membership-tiers/{$tierId}", [
                'discount_percent' => 18.0,
            ]);
        $updateRes->assertStatus(200)
            ->assertJsonPath('data.discount_percent', 18);

        // Delete
        $deleteRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/membership-tiers/{$tierId}");
        $deleteRes->assertStatus(200);
    }

    public function test_admin_cannot_create_duplicate_membership_tier_rules(): void
    {
        // Try creating duplicate level (level 1 already exists)
        $resLevel = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/membership-tiers', [
                'level' => 1,
                'name_vi' => 'Cấp Mới 1',
                'name_en' => 'New Level 1',
                'min_deposit' => 9999.0,
                'discount_percent' => 5.0,
            ]);
        $resLevel->assertStatus(422)
            ->assertJsonValidationErrors(['level']);

        // Try creating duplicate min_deposit (100.0 already exists for Silver)
        $resDeposit = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/membership-tiers', [
                'level' => 99,
                'name_vi' => 'Cấp Mới 2',
                'name_en' => 'New Level 2',
                'min_deposit' => 100.0,
                'discount_percent' => 5.0,
            ]);
        $resDeposit->assertStatus(422)
            ->assertJsonValidationErrors(['min_deposit']);

        // Try creating duplicate name_vi ('Bronze' tier already exists)
        $resName = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/membership-tiers', [
                'level' => 99,
                'name_vi' => 'Đồng',
                'name_en' => 'Bronze Unique',
                'min_deposit' => 9999.0,
                'discount_percent' => 5.0,
            ]);
        $resName->assertStatus(422)
            ->assertJsonValidationErrors(['name_vi']);

        // Try creating Level 6 with min_deposit = 900 (smaller than Level 5 = 1000) -> Must be rejected with 422!
        $resProgression = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/membership-tiers', [
                'level' => 6,
                'name_vi' => 'Tinh Anh Thất Bại',
                'name_en' => 'Failed Elite',
                'min_deposit' => 900.0,
                'discount_percent' => 20.0,
            ]);
        $resProgression->assertStatus(422)
            ->assertJsonValidationErrors(['min_deposit']);
    }

    public function test_adjust_cumulative_deposit_updates_user_tier_automatically(): void
    {
        // Initially total_deposited is 0 (Bronze Tier)
        $user = $this->customerModel->fresh();
        app(\App\Application\Services\MembershipTierService::class)->recalculateUserTier($user);
        $user->refresh();
        $this->assertSame('Đồng', $user->membershipTier->name_vi);

        // Admin adds $100 -> Reaches Silver
        $res = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/users/{$this->customerModel->id}/adjust-cumulative-deposit", [
                'type' => 'add',
                'amount' => 100.0,
            ]);

        $res->assertStatus(200)
            ->assertJsonPath('user.total_deposited', 100)
            ->assertJsonPath('user.membership_tier.name_vi', 'Bạc');

        // Admin adds $900 -> Total $1000 -> Reaches Diamond
        $res2 = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/users/{$this->customerModel->id}/adjust-cumulative-deposit", [
                'type' => 'add',
                'amount' => 900.0,
            ]);

        $res2->assertStatus(200)
            ->assertJsonPath('user.total_deposited', 1000)
            ->assertJsonPath('user.membership_tier.name_vi', 'Kim cương');

        // Admin subtracts $600 -> Total $400 -> Drops to Gold
        $res3 = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/users/{$this->customerModel->id}/adjust-cumulative-deposit", [
                'type' => 'subtract',
                'amount' => 600.0,
            ]);

        $res3->assertStatus(200)
            ->assertJsonPath('user.total_deposited', 400)
            ->assertJsonPath('user.membership_tier.name_vi', 'Vàng');
    }

    public function test_order_creation_applies_tier_discount(): void
    {
        // 1. Set user balance to 100 and total_deposited to 1000 (Diamond - 10% discount)
        $this->customerModel->balance = 100.0;
        $this->customerModel->total_deposited = 1000.0;
        $this->customerModel->save();
        app(\App\Application\Services\MembershipTierService::class)->recalculateUserTier($this->customerModel);

        $authService = app(\App\Application\Services\AuthService::class);
        $customerToken = $authService->generateAccessToken(UserMapper::toDomain($this->customerModel->fresh()));

        // 2. Create product
        $category = CategoryModel::create([
            'id' => (string) Str::uuid7(),
            'name' => 'General Accounts',
            'slug' => 'general-accounts',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $product = ProductModel::create([
            'id' => (string) Str::uuid7(),
            'category_id' => $category->id,
            'name' => 'Test Item',
            'slug' => 'test-item',
            'price' => 10.0, // 10 USD per item
            'description' => 'Test Item Description',
            'available_count' => 10,
            'is_active' => true,
        ]);

        $batch = \App\Infrastructure\Persistence\Eloquent\InventoryBatchModel::create([
            'id' => (string) Str::uuid7(),
            'product_id' => $product->id,
            'batch_name' => 'Batch 1',
            'initial_count' => 2,
            'current_count' => 2,
        ]);

        // Add inventory
        InventoryItemModel::create([
            'id' => (string) Str::uuid7(),
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'data' => 'acc_data_1',
            'secret_data' => 'acc_data_1',
            'status' => 'available',
        ]);
        InventoryItemModel::create([
            'id' => (string) Str::uuid7(),
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'data' => 'acc_data_2',
            'secret_data' => 'acc_data_2',
            'status' => 'available',
        ]);

        // Buy 2 items: Raw Total = 2 * 10 = $20.
        // Diamond tier discount = 10% -> Discount = $2.0. Total to pay = $18.0.
        $res = $this->withHeader('Authorization', "Bearer {$customerToken}")
            ->postJson('/api/v1/orders', [
                'idempotency_key' => (string) Str::uuid7(),
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
            ]);

        $res->assertStatus(201)
            ->assertJsonPath('order.total_price', 18)
            ->assertJsonPath('order.discount_amount', 2);
    }

    public function test_b2b_profile_returns_tier_and_total_deposited(): void
    {
        $this->customerModel->balance = 500.0;
        $this->customerModel->total_deposited = 1000.0;
        $this->customerModel->api_key = 'b2b_test_api_key_secret';
        $this->customerModel->save();
        app(\App\Application\Services\MembershipTierService::class)->recalculateUserTier($this->customerModel);

        $res = $this->withHeader('X-API-KEY', 'b2b_test_api_key_secret')
            ->getJson('/api/v1/b2b/me');

        $res->assertStatus(200)
            ->assertJsonPath('total_deposited', 1000)
            ->assertJsonPath('membership_tier.name_vi', 'Kim cương')
            ->assertJsonPath('membership_tier.discount_percent', 10);
    }

    public function test_disabling_membership_tiers_system_ignores_tier_discount(): void
    {
        // 1. Disable membership tiers system via SystemSetting
        app(\App\Application\Services\SystemSettingService::class)->setSetting('membership_tiers_enabled', '0');

        // 2. Customer has Diamond tier (10% discount) and $100 balance
        $this->customerModel->balance = 100.0;
        $this->customerModel->total_deposited = 1000.0;
        $this->customerModel->save();
        app(\App\Application\Services\MembershipTierService::class)->recalculateUserTier($this->customerModel);

        $authService = app(\App\Application\Services\AuthService::class);
        $customerToken = $authService->generateAccessToken(UserMapper::toDomain($this->customerModel->fresh()));

        $category = CategoryModel::create([
            'id' => (string) Str::uuid7(),
            'name' => 'Disabled Tier Category',
            'slug' => 'disabled-tier-category',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $product = ProductModel::create([
            'id' => (string) Str::uuid7(),
            'category_id' => $category->id,
            'name' => 'Disabled Tier Product',
            'slug' => 'disabled-tier-product',
            'price' => 10.0,
            'description' => 'Test Product',
            'available_count' => 5,
            'is_active' => true,
        ]);

        $batch = \App\Infrastructure\Persistence\Eloquent\InventoryBatchModel::create([
            'id' => (string) Str::uuid7(),
            'product_id' => $product->id,
            'batch_name' => 'Batch Disabled Tier',
            'initial_count' => 2,
            'current_count' => 2,
        ]);

        InventoryItemModel::create([
            'id' => (string) Str::uuid7(),
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'data' => 'disabled_tier_1',
            'secret_data' => 'disabled_tier_1',
            'status' => 'available',
        ]);

        // When system is DISABLED, 2 items * $10 = $20 (NO discount applied)
        $res = $this->withHeader('Authorization', "Bearer {$customerToken}")
            ->postJson('/api/v1/orders', [
                'idempotency_key' => (string) Str::uuid7(),
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $res->assertStatus(201)
            ->assertJsonPath('order.total_price', 10)
            ->assertJsonPath('order.discount_amount', 0);
    }

    public function test_disabling_feature_in_env_blocks_api_routes_with_403(): void
    {
        config(['app.feature_membership_tiers_enabled' => false]);

        $res = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/membership-tiers');

        $res->assertStatus(403)
            ->assertJsonPath('message', 'The Membership Tiers feature is disabled in the server environment configuration (.env FEATURE_MEMBERSHIP_TIERS_ENABLED=false).');
    }

    public function test_disabling_feature_in_env_behaves_safely_across_all_endpoints(): void
    {
        config(['app.feature_membership_tiers_enabled' => false]);

        // 1. Public settings returns feature disabled flag
        $resPublicSettings = $this->getJson('/api/v1/settings/public');
        $resPublicSettings->assertStatus(200)
            ->assertJsonPath('membership_tiers_feature_enabled', false)
            ->assertJsonPath('membership_tiers_enabled', false);

        // 2. Public tiers returns empty list safely
        $resPublicTiers = $this->getJson('/api/v1/membership-tiers');
        $resPublicTiers->assertStatus(200)
            ->assertJsonPath('data', []);

        // 3. User profile & Auth /me return null tier safely
        $resMe = $this->withHeader('Authorization', "Bearer {$this->customerToken}")
            ->getJson('/api/v1/auth/me');
        $resMe->assertStatus(200)
            ->assertJsonPath('user.membership_tier', null);

        // 4. B2B /me returns null tier safely
        $this->customerModel->api_key = 'b2b_disabled_feature_key';
        $this->customerModel->save();
        $resB2b = $this->withHeader('X-API-KEY', 'b2b_disabled_feature_key')
            ->getJson('/api/v1/b2b/me');
        $resB2b->assertStatus(200)
            ->assertJsonPath('membership_tier', null);

        // 5. Admin adjust cumulative deposit is blocked with 403 Forbidden when feature is disabled in .env
        $resAdjustDep = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/users/{$this->customerModel->id}/adjust-cumulative-deposit", [
                'type' => 'add',
                'amount' => 500.0,
            ]);
        $resAdjustDep->assertStatus(403);

        // 6. Admin adjust balance handles gracefully without 500 error
        $resAdjustBal = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/users/{$this->customerModel->id}/adjust-balance", [
                'type' => 'add',
                'amount' => 100.0,
                'affect_cumulative_deposit' => true,
            ]);
        $resAdjustBal->assertStatus(200)
            ->assertJsonPath('user.membership_tier', null);
    }
}

