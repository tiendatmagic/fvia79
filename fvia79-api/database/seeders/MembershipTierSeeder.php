<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\MembershipTierModel;
use Illuminate\Database\Seeder;

class MembershipTierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'id' => '019f9b90-78bc-706f-acbd-743b5db68000',
                'level' => 1,
                'name_vi' => 'Đồng',
                'name_en' => 'Bronze',
                'min_deposit' => 0.0,
                'discount_percent' => 0.0,
                'icon' => 'shield',
                'color' => '#94a3b8',
                'is_active' => true,
            ],
            [
                'id' => '019f9b90-78da-7194-9258-d634808f3c0a',
                'level' => 2,
                'name_vi' => 'Bạc',
                'name_en' => 'Silver',
                'min_deposit' => 100.0,
                'discount_percent' => 2.0,
                'icon' => 'shield-check',
                'color' => '#38bdf8',
                'is_active' => true,
            ],
            [
                'id' => '019f9b90-78df-72f9-86d0-6e25b2e566a2',
                'level' => 3,
                'name_vi' => 'Vàng',
                'name_en' => 'Gold',
                'min_deposit' => 300.0,
                'discount_percent' => 5.0,
                'icon' => 'sparkles',
                'color' => '#eab308',
                'is_active' => true,
            ],
            [
                'id' => '019f9b90-78e5-70e1-b1ed-75f6c4be7dff',
                'level' => 4,
                'name_vi' => 'Bạch kim',
                'name_en' => 'Platinum',
                'min_deposit' => 500.0,
                'discount_percent' => 8.0,
                'icon' => 'trophy',
                'color' => '#a855f7',
                'is_active' => true,
            ],
            [
                'id' => '019f9b90-78ea-70d1-bf87-aa18adce9666',
                'level' => 5,
                'name_vi' => 'Kim cương',
                'name_en' => 'Diamond',
                'min_deposit' => 1000.0,
                'discount_percent' => 10.0,
                'icon' => 'diamond',
                'color' => '#06b6d4',
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tier) {
            MembershipTierModel::updateOrCreate(
                ['level' => $tier['level']],
                [
                    'id' => $tier['id'],
                    'name_vi' => $tier['name_vi'],
                    'name_en' => $tier['name_en'],
                    'min_deposit' => $tier['min_deposit'],
                    'discount_percent' => $tier['discount_percent'],
                    'icon' => $tier['icon'],
                    'color' => $tier['color'],
                    'is_active' => $tier['is_active'],
                ]
            );
        }
    }
}