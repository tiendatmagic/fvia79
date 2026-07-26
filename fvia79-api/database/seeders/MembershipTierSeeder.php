<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\MembershipTierModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MembershipTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'level' => 1,
                'name_vi' => 'Đồng',
                'name_en' => 'Bronze',
                'min_deposit' => 0.0,
                'discount_percent' => 0.0,
                'icon' => 'shield',
                'color' => '#94a3b8', // slate-400
                'is_active' => true,
            ],
            [
                'level' => 2,
                'name_vi' => 'Bạc',
                'name_en' => 'Silver',
                'min_deposit' => 100.0,
                'discount_percent' => 2.0,
                'icon' => 'shield-check',
                'color' => '#38bdf8', // sky-400
                'is_active' => true,
            ],
            [
                'level' => 3,
                'name_vi' => 'Vàng',
                'name_en' => 'Gold',
                'min_deposit' => 300.0,
                'discount_percent' => 5.0,
                'icon' => 'sparkles',
                'color' => '#eab308', // yellow-500
                'is_active' => true,
            ],
            [
                'level' => 4,
                'name_vi' => 'Bạch kim',
                'name_en' => 'Platinum',
                'min_deposit' => 500.0,
                'discount_percent' => 8.0,
                'icon' => 'trophy',
                'color' => '#a855f7', // purple-500
                'is_active' => true,
            ],
            [
                'level' => 5,
                'name_vi' => 'Kim cương',
                'name_en' => 'Diamond',
                'min_deposit' => 1000.0,
                'discount_percent' => 10.0,
                'icon' => 'diamond',
                'color' => '#06b6d4', // cyan-500
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tier) {
            MembershipTierModel::updateOrCreate(
                ['level' => $tier['level']],
                [
                    'id' => (string) Str::uuid7(),
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
