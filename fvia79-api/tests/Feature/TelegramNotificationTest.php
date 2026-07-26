<?php

namespace Tests\Feature;

use App\Application\Services\AuthService;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryBatchModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function generateToken(UserModel $user): string
    {
        $authService = app(AuthService::class);
        $userEntity = UserMapper::toDomain($user);
        $session = $authService->createSession($userEntity, '127.0.0.1', 'PHPUnit');

        return $authService->generateAccessToken($userEntity, $session->id);
    }

    public function test_telegram_bot_token_is_automatically_encrypted_in_database(): void
    {
        SystemSettingModel::create([
            'key' => 'telegram_bot_token',
            'value' => '8829514283:AAFZ1qpXqqPbJjVwHa4jtBIRDmbm52jVEMA',
        ]);

        $rawRow = \Illuminate\Support\Facades\DB::table('system_settings')
            ->where('key', 'telegram_bot_token')
            ->first();

        $this->assertNotNull($rawRow);
        $this->assertNotEquals('8829514283:AAFZ1qpXqqPbJjVwHa4jtBIRDmbm52jVEMA', $rawRow->value);
        $this->assertEquals('8829514283:AAFZ1qpXqqPbJjVwHa4jtBIRDmbm52jVEMA', Crypt::decryptString($rawRow->value));

        $model = SystemSettingModel::where('key', 'telegram_bot_token')->first();
        $this->assertEquals('8829514283:AAFZ1qpXqqPbJjVwHa4jtBIRDmbm52jVEMA', $model->value);
    }

    public function test_webhook_automatically_links_user_account_on_start_cmd(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
        ]);

        SystemSettingModel::create([
            'key' => 'telegram_bot_token',
            'value' => '8829514283:AAFZ1qpXqqPbJjVwHa4jtBIRDmbm52jVEMA',
        ]);
        SystemSettingModel::create([
            'key' => 'telegram_bot_enabled',
            'value' => '1',
        ]);

        $user = UserModel::create([
            'uid' => '10000005',
            'username' => 'testuser05',
            'name' => 'Test User',
            'email' => 'user05@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'google2fa_enabled' => false,
            'telegram_chat_id' => null,
        ]);

        $response = $this->postJson('/api/v1/telegram/webhook', [
            'update_id' => 123456,
            'message' => [
                'message_id' => 1,
                'from' => ['id' => 987654321, 'username' => 'testuser'],
                'chat' => ['id' => 987654321, 'type' => 'private'],
                'text' => '/start 10000005',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'connected', 'uid' => '10000005']);

        $this->assertEquals('987654321', $user->fresh()->telegram_chat_id);
        $this->assertTrue((bool) $user->fresh()->telegram_notifications_enabled);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/sendMessage') && str_contains($request->body(), '987654321');
        });
    }

    public function test_user_can_view_update_and_test_telegram_settings(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
        ]);

        SystemSettingModel::create([
            'key' => 'telegram_bot_token',
            'value' => '8829514283:AAFZ1qpXqqPbJjVwHa4jtBIRDmbm52jVEMA',
        ]);
        SystemSettingModel::create([
            'key' => 'telegram_bot_username',
            'value' => 'fvia79noti_bot',
        ]);

        $user = UserModel::create([
            'uid' => '10000008',
            'username' => 'testuser08',
            'name' => 'Test User 08',
            'email' => 'user08@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'google2fa_enabled' => false,
            'telegram_chat_id' => null,
        ]);

        $token = $this->generateToken($user);

        $getStatus = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/user/telegram');

        $getStatus->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'is_connected' => false,
                    'bot_username' => 'fvia79noti_bot',
                    'connect_url' => 'https://t.me/fvia79noti_bot?start=10000008',
                ],
            ]);

        $update = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/user/telegram', [
                'telegram_chat_id' => '555666777',
                'telegram_notifications_enabled' => true,
            ]);

        $update->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'telegram_chat_id' => '555666777',
                    'is_connected' => true,
                ],
            ]);

        $this->assertEquals('555666777', $user->fresh()->telegram_chat_id);

        $testMsg = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/user/telegram/test');

        $testMsg->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $disconnect = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/user/telegram');

        $disconnect->assertStatus(200);
        $this->assertNull($user->fresh()->telegram_chat_id);
    }

    public function test_new_inventory_batch_triggers_telegram_notification_once(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
        ]);

        SystemSettingModel::create([
            'key' => 'telegram_bot_token',
            'value' => '8829514283:AAFZ1qpXqqPbJjVwHa4jtBIRDmbm52jVEMA',
        ]);
        SystemSettingModel::create([
            'key' => 'telegram_bot_enabled',
            'value' => '1',
        ]);

        // u1: linked Telegram + purchased product within 6 months -> eligible
        $u1 = UserModel::create(['username' => 'u1', 'name' => 'U1', 'email' => 'u1@ex.com', 'password' => Hash::make('password123'), 'role' => 'customer', 'google2fa_enabled' => false, 'telegram_chat_id' => '1154171881', 'telegram_notifications_enabled' => true]);
        // u2: linked Telegram + purchased product within 6 months -> eligible
        $u2 = UserModel::create(['username' => 'u2', 'name' => 'U2', 'email' => 'u2@ex.com', 'password' => Hash::make('password123'), 'role' => 'customer', 'google2fa_enabled' => false, 'telegram_chat_id' => '222222', 'telegram_notifications_enabled' => true]);
        // u3: missing telegram_chat_id -> ineligible
        UserModel::create(['username' => 'u3', 'name' => 'U3', 'email' => 'u3@ex.com', 'password' => Hash::make('password123'), 'role' => 'customer', 'google2fa_enabled' => false, 'telegram_chat_id' => null]);
        // u4: linked Telegram but hasn't purchased product -> ineligible
        UserModel::create(['username' => 'u4', 'name' => 'U4', 'email' => 'u4@ex.com', 'password' => Hash::make('password123'), 'role' => 'customer', 'google2fa_enabled' => false, 'telegram_chat_id' => '444444', 'telegram_notifications_enabled' => true]);

        $admin = UserModel::create(['username' => 'adm', 'name' => 'Admin', 'email' => 'adm@ex.com', 'password' => Hash::make('password123'), 'role' => 'admin', 'google2fa_enabled' => false]);
        $adminToken = $this->generateToken($admin);

        $category = CategoryModel::create([
            'name' => 'Category Test',
            'slug' => 'category-test',
            'is_active' => true,
        ]);

        $product = ProductModel::create([
            'category_id' => $category->id,
            'name' => 'BM Facebook 50$',
            'slug' => 'bm-facebook-50',
            'description' => 'BM Desc',
            'price' => 50000,
            'status' => 'active',
        ]);

        // Create orders within 6 months for u1 and u2 with this product
        $orderU1 = OrderModel::create(['user_id' => $u1->id, 'order_code' => 'ORD-U1', 'total_price' => 50000, 'status' => 'completed']);
        OrderItemModel::create(['order_id' => $orderU1->id, 'product_id' => $product->id, 'product_name' => 'BM Facebook 50$', 'price' => 50000, 'quantity' => 1]);

        $orderU2 = OrderModel::create(['user_id' => $u2->id, 'order_code' => 'ORD-U2', 'total_price' => 50000, 'status' => 'completed']);
        OrderItemModel::create(['order_id' => $orderU2->id, 'product_id' => $product->id, 'product_name' => 'BM Facebook 50$', 'price' => 50000, 'quantity' => 1]);

        $response = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson('/api/v1/inventory/import', [
                'product_id' => $product->id,
                'batch_name' => 'KHO-HANG-HOM-NAY',
                'raw_data' => "user1|pass1\nuser2|pass2",
            ]);

        $response->assertStatus(201);

        // Only u1 and u2 receive notifications (2 Telegram API calls)
        Http::assertSentCount(2);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'BM Facebook 50$') && str_contains($request->body(), '1154171881');
        });
        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'BM Facebook 50$') && str_contains($request->body(), '222222');
        });
    }

    public function test_order_success_triggers_telegram_notification_without_resource_data(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
        ]);

        SystemSettingModel::create([
            'key' => 'telegram_bot_token',
            'value' => '8829514283:AAFZ1qpXqqPbJjVwHa4jtBIRDmbm52jVEMA',
        ]);
        SystemSettingModel::create([
            'key' => 'telegram_bot_enabled',
            'value' => '1',
        ]);

        $user = UserModel::create([
            'username' => 'cust',
            'name' => 'Customer',
            'email' => 'cust@ex.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'google2fa_enabled' => false,
            'balance' => 100.0,
            'telegram_chat_id' => '999888777',
            'telegram_notifications_enabled' => true,
        ]);
        $userToken = $this->generateToken($user);

        $category = CategoryModel::create([
            'name' => 'Category Test',
            'slug' => 'category-test',
            'is_active' => true,
        ]);

        $product = ProductModel::create([
            'category_id' => $category->id,
            'name' => 'Tài khoản Via Facebook 2023',
            'slug' => 'tai-khoan-via-facebook-2023',
            'description' => 'Via Desc',
            'price' => 10.0,
            'status' => 'active',
        ]);

        app(\App\Application\Services\InventoryService::class)->importBatch(
            $product->id,
            'Batch Secret',
            "SECRET_VIA_ACCOUNT_001|pass123\nSECRET_VIA_ACCOUNT_002|pass456"
        );

        $response = $this->withHeader('Authorization', "Bearer {$userToken}")
            ->postJson('/api/v1/orders', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1]
                ],
                'idempotency_key' => (string) Str::uuid(),
            ]);

        $response->assertStatus(201);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/sendMessage')
                && (string) $request['chat_id'] === '999888777'
                && str_contains($request['text'], 'Tài khoản Via Facebook 2023')
                && !str_contains($request['text'], 'SECRET_VIA_ACCOUNT_001')
                && !str_contains($request['text'], 'pass123');
        });
    }
}
