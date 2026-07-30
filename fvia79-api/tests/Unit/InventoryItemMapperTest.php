<?php

namespace Tests\Unit;

use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Mappers\InventoryItemMapper;
use Tests\TestCase;

class InventoryItemMapperTest extends TestCase
{
    public function test_it_maps_a_database_sold_at_value_to_a_domain_date(): void
    {
        $model = new InventoryItemModel;
        $model->setRawAttributes([
            'id' => '019f7d2f-cf44-723f-8c30-13b7ea78dadb',
            'batch_id' => '019f7d2f-cf44-723f-8c30-13b7ea78dac',
            'product_id' => '019f7d2f-cf44-723f-8c30-13b7ea78dad',
            'secret_data' => 'account@example.com|password',
            'status' => 'sold',
            'order_id' => null,
            'sold_at' => '2026-07-30 18:49:06',
            'created_at' => '2026-07-30 18:00:00',
            'updated_at' => '2026-07-30 18:49:06',
        ], true);

        $item = InventoryItemMapper::toDomain($model);

        $this->assertSame('2026-07-30 18:49:06', $item->getSoldAt()?->format('Y-m-d H:i:s'));
    }
}
