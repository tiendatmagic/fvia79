<?php

namespace Tests\Feature;

use App\Application\Services\AuthService;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Database\Seeders\WarrantyPolicyAndFaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WarrantyPolicyAndFaqSeeder::class);
    }

    private function createTokenForUser(UserModel $user): string
    {
        $domainUser = UserMapper::toDomain($user);
        $authService = app(AuthService::class);
        $session = $authService->createSession($domainUser, '127.0.0.1', 'TestAgent');
        return $authService->generateAccessToken($domainUser, $session->id);
    }

    public function test_public_can_fetch_active_faqs_and_warranty_policies(): void
    {
        $response = $this->getJson('/api/v1/faqs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'categories' => [
                    '*' => [
                        'id',
                        'key',
                        'name_vi',
                        'name_en',
                        'icon',
                        'is_active',
                    ],
                ],
                'policies' => [
                    '*' => [
                        'id',
                        'category',
                        'type',
                        'content_vi',
                        'content_en',
                        'is_active',
                    ],
                ],
                'faqs' => [
                    '*' => [
                        'id',
                        'question_vi',
                        'question_en',
                        'answer_vi',
                        'answer_en',
                        'is_active',
                    ],
                ],
            ]);
    }

    public function test_admin_can_create_update_and_delete_policy_category(): void
    {
        $admin = UserModel::create([
            'username' => 'admin_test_cat',
            'name' => 'Admin Cat Tester',
            'email' => 'admin_test_cat@example.com',
            'password' => Hash::make('Password123!@#'),
            'role' => 'admin',
            'balance' => 0,
            'google2fa_enabled' => false,
        ]);

        $token = $this->createTokenForUser($admin);

        // Store Category
        $response = $this->withToken($token)
            ->postJson('/api/v1/admin/policy-categories', [
                'name_vi' => 'Tài khoản Gmail',
                'name_en' => 'Gmail Accounts',
                'icon' => 'mail',
                'sort_order' => 30,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.key', 'gmail_accounts');

        $catId = $response->json('data.id');

        // Update Category
        $this->withToken($token)
            ->putJson("/api/v1/admin/policy-categories/{$catId}", [
                'name_vi' => 'Tài khoản Mail Premium',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.name_vi', 'Tài khoản Mail Premium');

        // Delete Category
        $this->withToken($token)
            ->deleteJson("/api/v1/admin/policy-categories/{$catId}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('policy_categories', ['id' => $catId]);
    }

    public function test_admin_can_create_update_and_delete_warranty_policy(): void
    {
        $admin = UserModel::create([
            'username' => 'admin_test_faq',
            'name' => 'Admin FAQ Tester',
            'email' => 'admin_test_faq@example.com',
            'password' => Hash::make('Password123!@#'),
            'role' => 'admin',
            'balance' => 0,
            'google2fa_enabled' => false,
        ]);

        $token = $this->createTokenForUser($admin);

        // Store
        $response = $this->withToken($token)
            ->postJson('/api/v1/admin/warranty-policies', [
                'category' => 'bm',
                'type' => 'accepted',
                'content_vi' => 'BM bảo hành 30 ngày',
                'content_en' => 'BM 30-day warranty',
                'tag_vi' => '30 ngày',
                'tag_en' => '30 days',
                'tag_color' => 'emerald',
                'sort_order' => 10,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $policyId = $response->json('data.id');

        // Update
        $this->withToken($token)
            ->putJson("/api/v1/admin/warranty-policies/{$policyId}", [
                'content_vi' => 'BM bảo hành 60 ngày',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.content_vi', 'BM bảo hành 60 ngày');

        // Delete
        $this->withToken($token)
            ->deleteJson("/api/v1/admin/warranty-policies/{$policyId}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('warranty_policies', ['id' => $policyId]);
    }

    public function test_admin_can_create_update_and_delete_faq(): void
    {
        $admin = UserModel::create([
            'username' => 'admin_test_faq_2',
            'name' => 'Admin FAQ Tester 2',
            'email' => 'admin_test_faq_2@example.com',
            'password' => Hash::make('Password123!@#'),
            'role' => 'admin',
            'balance' => 0,
            'google2fa_enabled' => false,
        ]);

        $token = $this->createTokenForUser($admin);

        // Store
        $response = $this->withToken($token)
            ->postJson('/api/v1/admin/faqs', [
                'question_vi' => 'Câu hỏi thử nghiệm?',
                'question_en' => 'Test question?',
                'answer_vi' => 'Trả lời thử nghiệm',
                'answer_en' => 'Test answer',
                'sort_order' => 5,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $faqId = $response->json('data.id');

        // Update
        $this->withToken($token)
            ->putJson("/api/v1/admin/faqs/{$faqId}", [
                'answer_vi' => 'Trả lời mới',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.answer_vi', 'Trả lời mới');

        // Delete
        $this->withToken($token)
            ->deleteJson("/api/v1/admin/faqs/{$faqId}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('faqs', ['id' => $faqId]);
    }

    public function test_non_admin_user_cannot_manage_faqs(): void
    {
        $user = UserModel::create([
            'username' => 'user_test_faq',
            'name' => 'User FAQ Tester',
            'email' => 'user_test_faq@example.com',
            'password' => Hash::make('Password123!@#'),
            'role' => 'customer',
            'balance' => 0,
            'google2fa_enabled' => false,
        ]);

        $token = $this->createTokenForUser($user);

        $this->withToken($token)
            ->postJson('/api/v1/admin/warranty-policies', [
                'category' => 'bm',
                'type' => 'accepted',
                'content_vi' => 'Test',
                'content_en' => 'Test',
            ])
            ->assertStatus(403);
    }

    public function test_flow_create_new_policy_category_and_policy_and_verify_public_api(): void
    {
        $admin = UserModel::create([
            'username' => 'admin_flow_tester',
            'name' => 'Admin Flow Tester',
            'email' => 'admin_flow_tester@example.com',
            'password' => Hash::make('Password123!@#'),
            'role' => 'admin',
            'balance' => 0,
            'google2fa_enabled' => false,
        ]);

        $token = $this->createTokenForUser($admin);

        // Step 1: Create new applicable category "Residential Proxy"
        $catResponse = $this->withToken($token)
            ->postJson('/api/v1/admin/policy-categories', [
                'key' => 'proxy_dan_cu',
                'name_vi' => 'Proxy Dân Cư',
                'name_en' => 'Residential Proxy',
                'icon' => 'globe',
                'sort_order' => 30,
                'is_active' => true,
            ]);

        $catResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.key', 'proxy_dan_cu');

        $categoryKey = $catResponse->json('data.key');

        // Step 2: Create warranty accepted policy for "Residential Proxy"
        $acceptedPolicyResponse = $this->withToken($token)
            ->postJson('/api/v1/admin/warranty-policies', [
                'category' => $categoryKey,
                'type' => 'accepted',
                'content_vi' => 'Bảo hành 1 đổi 1 trong 24 giờ nếu IP Proxy bị hỏng kết nối',
                'content_en' => '1-to-1 warranty replacement within 24h if Proxy IP connection fails',
                'tag_vi' => '24 giờ',
                'tag_en' => '24 hours',
                'tag_color' => 'emerald',
                'sort_order' => 10,
                'is_active' => true,
            ]);

        $acceptedPolicyResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.category', 'proxy_dan_cu');

        // Step 3: Create warranty declined policy for "Residential Proxy"
        $rejectedPolicyResponse = $this->withToken($token)
            ->postJson('/api/v1/admin/warranty-policies', [
                'category' => $categoryKey,
                'type' => 'rejected',
                'content_vi' => 'Từ chối bảo hành nếu tự ý đỗi cấu hình Port hoặc Proxy bị lạm dụng spam',
                'content_en' => 'Warranty rejected if Port settings modified or Proxy abused for spam',
                'tag_vi' => 'Từ chối',
                'tag_en' => 'Rejected',
                'tag_color' => 'rose',
                'sort_order' => 20,
                'is_active' => true,
            ]);

        $rejectedPolicyResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.category', 'proxy_dan_cu');

        // Step 4: Call public API GET /api/v1/faqs to verify new category and policies appear in output
        $publicResponse = $this->getJson('/api/v1/faqs');

        $publicResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $categories = $publicResponse->json('categories');
        $policies = $publicResponse->json('policies');

        // Verify "Residential Proxy" category exists in list
        $foundCat = collect($categories)->firstWhere('key', 'proxy_dan_cu');
        $this->assertNotNull($foundCat, 'Residential Proxy category must exist in public API');
        $this->assertEquals('Proxy Dân Cư', $foundCat['name_vi']);
        $this->assertEquals('Residential Proxy', $foundCat['name_en']);

        // Verify policies belonging to "Residential Proxy"
        $proxyPolicies = collect($policies)->where('category', 'proxy_dan_cu')->values();
        $this->assertCount(2, $proxyPolicies, 'Must have 2 warranty policies under Residential Proxy category');
        $this->assertEquals('Bảo hành 1 đổi 1 trong 24 giờ nếu IP Proxy bị hỏng kết nối', $proxyPolicies[0]['content_vi']);
        $this->assertEquals('Từ chối bảo hành nếu tự ý đỗi cấu hình Port hoặc Proxy bị lạm dụng spam', $proxyPolicies[1]['content_vi']);
    }
}
