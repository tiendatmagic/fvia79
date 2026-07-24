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
}
