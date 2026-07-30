<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InventoryBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    private array $adminHeaders;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = UserModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a771',
            'username' => 'inventory_admin',
            'name' => 'Inventory Admin',
            'email' => 'inventory_admin@fvia79.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'balance' => 1000000.0,
        ]);

        $token = resolve(JwtService::class)->generateToken([
            'sub' => $admin->id,
            'email' => $admin->email,
            'role' => $admin->role,
        ], 1800);

        $this->adminHeaders = [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_bulk_resource_actions_and_manual_live_check_keep_batch_counts_in_sync(): void
    {
        Http::fake([
            'https://graph.facebook.com/100000000000001/picture*' => Http::response([
                'data' => ['height' => 50, 'is_silhouette' => false],
            ]),
            'https://graph.facebook.com/100000000000002/picture*' => Http::response([
                'error' => ['message' => 'Unsupported get request.'],
            ], 400),
        ]);

        $category = CategoryModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a773',
            'name' => 'Facebook Via',
            'slug' => 'facebook-via',
        ]);

        $product = ProductModel::create([
            'id' => '019f6699-e6d2-70f2-89bd-d3b93946a781',
            'category_id' => $category->id,
            'name' => 'Bulk Resource Product',
            'slug' => 'bulk-resource-product',
            'price' => 50000.0,
            'status' => 'active',
        ]);

        $importResponse = $this->withHeaders($this->adminHeaders)
            ->postJson('/api/v1/inventory/import', [
                'product_id' => $product->id,
                'batch_name' => 'Bulk Resource Batch',
                'raw_data' => implode("\n", [
                    '100000000000001|password1',
                    '100000000000002|password2',
                    'invalid-account|password3',
                ]),
            ])
            ->assertCreated();

        $batchId = $importResponse->json('data.id');
        $items = InventoryItemModel::where('batch_id', $batchId)
            ->orderBy('created_at')
            ->get();

        $this->withHeaders($this->adminHeaders)
            ->postJson('/api/v1/inventory/resource-items/bulk-status', [
                'resource_ids' => [$items[0]->id, $items[1]->id],
                'status' => 'sold',
            ])
            ->assertOk()
            ->assertJsonPath('updated_count', 2);

        $this->withHeaders($this->adminHeaders)
            ->postJson('/api/v1/inventory/resource-items/bulk-status', [
                'resource_ids' => [$items[0]->id],
                'status' => 'available',
            ])
            ->assertOk();

        $this->withHeaders($this->adminHeaders)
            ->postJson('/api/v1/inventory/resource-items/check-live', [
                'resource_ids' => $items->pluck('id')->all(),
            ])
            ->assertOk()
            ->assertJsonPath('data.live_count', 1)
            ->assertJsonPath('data.die_count', 2);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $items[0]->id,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $items[1]->id,
            'status' => 'sold',
        ]);

        $this->withHeaders($this->adminHeaders)
            ->postJson('/api/v1/inventory/resource-items/bulk-delete', [
                'resource_ids' => [$items[1]->id, $items[2]->id],
            ])
            ->assertOk()
            ->assertJsonPath('deleted_count', 2);

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $batchId,
            'initial_count' => 1,
            'sold_count' => 0,
            'status' => 'active',
        ]);
    }
}
