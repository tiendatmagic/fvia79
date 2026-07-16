<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo Admin và Khách hàng
        $admin = UserModel::create([
            'name'     => 'admin',
            'email'    => env('SEED_ADMIN_EMAIL', 'admin@isocalmarket.com'),
            'password' => Hash::make(env('SEED_ADMIN_PASSWORD') ?? 'admin'),
            'role'     => 'admin'
        ]);

        $customers = [];
        $customerNames = [
            'Nguyễn Văn A', 'Trần Thị B', 'Lê Văn C', 'Phạm Thị D', 'Hoàng Văn E',
            'Đỗ Minh F', 'Vũ Thị G', 'Phan Thanh H', 'Bùi Văn I', 'Ngô Thị K'
        ];

        foreach ($customerNames as $index => $name) {
            $email = 'customer' . ($index + 1) . '@isocalmarket.com';
            $customers[] = UserModel::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make('password'),
                'role'     => 'customer',
                'phone'    => '098' . rand(1000000, 9999999)
            ]);
        }

        // 2. Tạo 12 Danh mục MMO mẫu thực tế theo yêu cầu
        $categoriesData = [
            ['name' => 'BẢNG GIÁ BM', 'slug' => 'bang-gia-bm', 'description' => 'Business Manager chất lượng cao, ngâm lâu năm.'],
            ['name' => 'Via US Live Ads', 'slug' => 'via-us-live-ads', 'description' => 'Via Mỹ chuyên chạy quảng cáo, ngâm chuẩn local.'],
            ['name' => 'Via Kháng 902 & Chat Support', 'slug' => 'via-khang-902-chat-support', 'description' => 'Via đã kháng link 902 và có nút hỗ trợ support trực tuyến.'],
            ['name' => 'Via Chuyên Set Camp ( Tỉ lệ TỤT , HOLD , DIE , NEED THẤP)', 'slug' => 'via-chuyen-set-camp', 'description' => 'Dòng via chuyên biệt để lên chiến dịch hạn chế lỗi tối đa.'],
            ['name' => 'Ad account Limit 250$', 'slug' => 'ad-account-limit-250', 'description' => 'Tài khoản quảng cáo limit 250$ trâu bò.'],
            ['name' => 'Via Ngoại Live Ads (No2FA)', 'slug' => 'via-ngoai-live-ads-no2fa', 'description' => 'Via ngoại không cài bảo mật 2 lớp.'],
            ['name' => 'Via Ngoại Live Ads (2FA)', 'slug' => 'via-ngoai-live-ads-2fa', 'description' => 'Via ngoại có sẵn bảo mật 2FA và checkpoint mail.'],
            ['name' => 'Clone Global', 'slug' => 'clone-global', 'description' => 'Clone ngoại ngâm lâu năm phục vụ spam, seeding.'],
            ['name' => 'Clone Việt Nam', 'slug' => 'clone-viet-nam', 'description' => 'Clone Việt chất lượng tốt reg tay sạch.'],
            ['name' => 'Via Ngoại DIE ADS', 'slug' => 'via-ngoai-die-ads', 'description' => 'Via ngoại bị hạn chế quảng cáo thích hợp seeding, spy.'],
            ['name' => 'Hotmail Live V.V', 'slug' => 'hotmail-live-vv', 'description' => 'Hộp thư Hotmail/Outlook ngâm lâu năm hỗ trợ API.'],
            ['name' => 'Clone Instagram', 'slug' => 'clone-instagram', 'description' => 'Tài khoản Instagram ngâm phục vụ marketing.']
        ];

        $categoriesMap = [];
        foreach ($categoriesData as $cData) {
            $categoriesMap[$cData['slug']] = CategoryModel::create($cData);
        }

        // 3. Tạo 51 sản phẩm MMO thực tế thuộc các danh mục trên
        $productsData = [
            // 1. BẢNG GIÁ BM
            [
                'category_slug' => 'bang-gia-bm',
                'name' => 'BM3 Ngâm Full 3 Tài Khoản',
                'slug' => 'bm3-ngam-full-3-tkqc',
                'description' => 'BM3 Ngâm Full 3 Tài Khoản chất lượng cao.',
                'price' => 80000,
                'attributes' => [['label' => 'Loại', 'value' => 'BM3 Ngâm'], ['label' => 'Số TKQC', 'value' => '3 TKQC']]
            ],
            [
                'category_slug' => 'bang-gia-bm',
                'name' => 'BM5 SCAN Trống 5',
                'slug' => 'bm5-scan-trong-5',
                'description' => 'BM5 SCAN Trống 5 tài khoản quảng cáo.',
                'price' => 2900000,
                'attributes' => [['label' => 'Loại', 'value' => 'BM5 SCAN'], ['label' => 'Trạng thái', 'value' => 'Trống 5']]
            ],
            [
                'category_slug' => 'bang-gia-bm',
                'name' => 'BM5 Trống 3 | Tạo 2018|Kèm Page 6500 Like',
                'slug' => 'bm5-trong-3-tao-2018-kem-page-6500-like',
                'description' => 'Sẵn 2 Tài Khoản tổng Ngưỡng 150$|Trống 3 |Kèm Page 6500 Like|',
                'price' => 2800000,
                'attributes' => [['label' => 'Năm tạo', 'value' => '2018'], ['label' => 'Page kèm', 'value' => '6500 Like'], ['label' => 'Ngưỡng', 'value' => '150$']]
            ],
            [
                'category_slug' => 'bang-gia-bm',
                'name' => 'BM3 XÁC MINH DOANH NGHIỆP NGÂM FULL 3 TÀI KHOẢN(LÊN LIMIT NHANH)',
                'slug' => 'bm3-xmdn-ngam-full-3-tkqc',
                'description' => '📌 Đã tạo 3 TKQC 📌 Limit tăng theo chi tiêu cực nhanh 📌 Limit 50$ 📌 BM Verify Business 📌 Created: Jun/2026',
                'price' => 230000,
                'attributes' => [['label' => 'Limit', 'value' => '50$'], ['label' => 'Xác minh', 'value' => 'Verify Business'], ['label' => 'Created', 'value' => 'Jun/2026']]
            ],
            [
                'category_slug' => 'bang-gia-bm',
                'name' => 'BM1 CỔ GỐC SCAN TẠO KÈM PAGE SCAN',
                'slug' => 'bm1-co-goc-scan-kem-page',
                'description' => 'BM 1 CỔ GỐC SCAN TẠO TRỐNG 1 + KÈM PAGE SCAN',
                'price' => 400000,
                'attributes' => [['label' => 'Loại', 'value' => 'BM1 Cổ gốc'], ['label' => 'Page kèm', 'value' => 'Page Scan']]
            ],
            [
                'category_slug' => 'bang-gia-bm',
                'name' => 'BM 3 NGÂM FULL 4 TÀI KHOẢN',
                'slug' => 'bm3-ngam-full-4-tkqc',
                'description' => 'BM full 4 Tài Khoản ngâm trâu bò.',
                'price' => 90000,
                'attributes' => [['label' => 'Loại', 'value' => 'BM3 ngâm'], ['label' => 'Số TKQC', 'value' => '4 TKQC']]
            ],
            [
                'category_slug' => 'bang-gia-bm',
                'name' => 'BM3 NGÂM 1 THÁNG FULL 3 TÀI KHOẢN',
                'slug' => 'bm3-ngam-1-thang-full-3-tkqc',
                'description' => 'Soak BM full 3 account over 1 month.',
                'price' => 80000,
                'attributes' => [['label' => 'Thời gian ngâm', 'value' => '> 1 tháng'], ['label' => 'Số TKQC', 'value' => '3 TKQC']]
            ],

            // 2. Via US Live Ads
            [
                'category_slug' => 'via-us-live-ads',
                'name' => 'VIA US LIVE ADS KÈM BM1 (2FA )',
                'slug' => 'via-us-live-ads-kem-bm1-2fa',
                'description' => 'Friend:Random | LIVE ADS | Checkpoint Mail | Created: 2025-2026 | Chưa dính IP Việt|Kèm BM1|',
                'price' => 450000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'US (Mỹ)'], ['label' => 'Kèm theo', 'value' => 'BM1'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'via-us-live-ads',
                'name' => 'Via US LIVE ADS (2FA )',
                'slug' => 'via-us-live-ads-2fa',
                'description' => 'Friend:Random | LIVE ADS | Checkpoint Mail | Created: 2025-2026 | Chưa dính IP Việt',
                'price' => 400000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'US (Mỹ)'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'via-us-live-ads',
                'name' => 'Via United States Cổ chuẩn LOCAL ( 2FA )',
                'slug' => 'via-us-co-chuan-local-2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt',
                'price' => 550000,
                'attributes' => [['label' => 'Độ tuổi', 'value' => 'Cổ (2008-2021)'], ['label' => 'Bảo mật', 'value' => '2FA Local US']]
            ],

            // 3. Via Kháng 902 & Chat Support
            [
                'category_slug' => 'via-khang-902-chat-support',
                'name' => 'Via Colombia Cổ Kháng 902 Live Ads',
                'slug' => 'via-colombia-co-khang-902-live-ads',
                'description' => 'Green tick: 3 Lines | Tích ẩn | Ad account: Live | Created: 2008-2022',
                'price' => 216000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Colombia'], ['label' => 'Kháng', 'value' => '902 Tick xanh'], ['label' => 'TKQC', 'value' => 'Live Ads']]
            ],
            [
                'category_slug' => 'via-khang-902-chat-support',
                'name' => 'Via Colombia Cổ Kháng 902 Die Ads',
                'slug' => 'via-colombia-co-khang-902-die-ads',
                'description' => 'Green tick: 3 Lines | Tích ẩn | Ad account: Die | Created: 2008-2022',
                'price' => 144000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Colombia'], ['label' => 'Kháng', 'value' => '902 Tick xanh'], ['label' => 'TKQC', 'value' => 'Die Ads']]
            ],
            [
                'category_slug' => 'via-khang-902-chat-support',
                'name' => 'Via Indonesia Cổ Kháng 902 Live Ads',
                'slug' => 'via-indonesia-co-khang-902-live-ads',
                'description' => 'Green tick: 3 Lines | Tích ẩn | Ad account: Live | Created: 2008-2022',
                'price' => 216000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Indonesia'], ['label' => 'Kháng', 'value' => '902 Tick xanh']]
            ],
            [
                'category_slug' => 'via-khang-902-chat-support',
                'name' => 'Via Ngoại Cổ Kháng 902 Die Ads',
                'slug' => 'via-ngoai-co-khang-902-die-ads',
                'description' => 'Green tick: 3 Lines | Tích ẩn | Ad account: Die | Created: 2008-2024',
                'price' => 119000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Ngoại cổ'], ['label' => 'Trạng thái', 'value' => 'Kháng 902']]
            ],
            [
                'category_slug' => 'via-khang-902-chat-support',
                'name' => 'Via Philippines Cổ XMDT Limit 50$',
                'slug' => 'via-philippines-co-xmdt-50',
                'description' => 'Green tick: 2 Lines | Tích ẩn | Created: 2008-2023',
                'price' => 200000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Philippines'], ['label' => 'Xác minh', 'value' => 'XMDT 2 Lines']]
            ],
            [
                'category_slug' => 'via-khang-902-chat-support',
                'name' => 'Via Chat Support',
                'slug' => 'via-chat-support-fbook',
                'description' => 'Có thể Chat Support Facebook | Năm tạo: 2025 | Checkpoint Mail',
                'price' => 144000,
                'attributes' => [['label' => 'Hỗ trợ', 'value' => 'Chat Support FB'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],

            // 4. Via Chuyên Set Camp
            [
                'category_slug' => 'via-chuyen-set-camp',
                'name' => 'VIA CHUYÊN SET CAMP (tỉ lệ HOLD , Need , Die cực thấp)',
                'slug' => 'via-chuyen-set-camp-hold-low',
                'description' => 'Friend: 30-1000 | LIVE ADS | Checkpoint Mail | Chuyên lên camp voi/sạch.',
                'price' => 300000,
                'attributes' => [['label' => 'Đặc tính', 'value' => 'Chuyên Set Camp'], ['label' => 'Tỉ lệ tụt', 'value' => 'Cực thấp']]
            ],
            [
                'category_slug' => 'via-chuyen-set-camp',
                'name' => 'Full Via Limit 50$ -7 USD đã paid mồi (No2FA)',
                'slug' => 'full-via-limit-50-paid-moi-no2fa',
                'description' => 'TKQC đã camp mồi, paid mồi | Đã kháng 273 | Tiền tệ: -7 USD | Quốc gia US lách thuế',
                'price' => 216000,
                'attributes' => [['label' => 'Paid mồi', 'value' => 'Đã paid mồi'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-chuyen-set-camp',
                'name' => 'Full Via Limit 50$ +7 USD đã paid mồi (No2FA)',
                'slug' => 'full-via-limit-50-plus-7-paid-no2fa',
                'description' => 'TKQC đã camp mồi, paid mồi | Đã kháng 273 | Tiền tệ: +7 USD | Quốc gia US lách thuế',
                'price' => 250000,
                'attributes' => [['label' => 'Tiền tệ', 'value' => '+7 USD'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-chuyen-set-camp',
                'name' => 'Via Ngoại Limit 50$ Có TKQC Cổ (No2FA)',
                'slug' => 'via-ngoai-limit-50-tkqc-co-no2fa',
                'description' => 'Bao đổi tiền, múi giờ, quốc gia | TKQC Cổ tạo từ: 2010–2024 | Chưa dính IP Việt',
                'price' => 156000,
                'attributes' => [['label' => 'TKQC', 'value' => 'Cổ (2010-2024)'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-chuyen-set-camp',
                'name' => 'COMBO 1 Account + 9 TKCN 1M1 (TÀI KHOẢN TẠO 2022)',
                'slug' => 'combo-1-acc-9-tkcn-1m1-2022',
                'description' => 'Combo Account random country with 9 personal ad accounts 1m1 creat 2022',
                'price' => 800000,
                'attributes' => [['label' => 'Loại', 'value' => 'Combo Account'], ['label' => 'Số TKQC', 'value' => '9 TKCN 1M1']]
            ],

            // 5. Ad account Limit 250$
            [
                'category_slug' => 'ad-account-limit-250',
                'name' => 'Full Via Limit 200$',
                'slug' => 'full-via-limit-200-usd',
                'description' => 'Bao đổi tiền, múi giờ, quốc gia | Không bao tụt Limit | TKQC Cổ tạo từ 2010-2023',
                'price' => 240000,
                'attributes' => [['label' => 'Limit', 'value' => '200$'], ['label' => 'Bảo hành', 'value' => 'Bao đổi tiền, múi giờ']]
            ],
            [
                'category_slug' => 'ad-account-limit-250',
                'name' => 'Via Ngoại Limit 250$ Cổ Ngâm',
                'slug' => 'via-ngoai-limit-250-co-ngam',
                'description' => 'Via Ngoại limit 250$ cổ ngâm trên 6 tháng.',
                'price' => 350000,
                'attributes' => [['label' => 'Limit', 'value' => '250$'], ['label' => 'Trạng thái', 'value' => 'Cổ ngâm']]
            ],

            // 6. Via Ngoại Live Ads (No2FA)
            [
                'category_slug' => 'via-ngoai-live-ads-no2fa',
                'name' => 'Via Ngoại Cổ (No2FA)',
                'slug' => 'via-ngoai-co-no2fa',
                'description' => 'Friend: 30-1000 | LIVE ADS | Checkpoint Mail | Created: 2008-2023',
                'price' => 40000,
                'attributes' => [['label' => 'Độ tuổi', 'value' => 'Cổ (2008-2023)'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-no2fa',
                'name' => 'Via Colombia Cổ (No2FA)',
                'slug' => 'via-colombia-co-no2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2008-2022 | Chưa dính IP Việt',
                'price' => 72000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Colombia'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-no2fa',
                'name' => 'Via Ecuador New (No2FA)',
                'slug' => 'via-ecuador-new-no2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt',
                'price' => 72000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Ecuador'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-no2fa',
                'name' => 'Via Brazil Cổ (No2FA)',
                'slug' => 'via-brazil-co-no2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt',
                'price' => 144000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Brazil'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-no2fa',
                'name' => 'Via Peru New (No2FA)',
                'slug' => 'via-peru-new-no2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt',
                'price' => 84000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Peru'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-no2fa',
                'name' => 'Via Philippines Cổ (No2FA)',
                'slug' => 'via-philippines-co-no2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt',
                'price' => 72000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Philippines'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-no2fa',
                'name' => 'Via Indonesia Cổ (No2FA)',
                'slug' => 'via-indonesia-co-no2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2008-2023 | Chưa dính IP Việt',
                'price' => 72000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Indonesia'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-no2fa',
                'name' => 'Via Bangladesh New (No2FA)',
                'slug' => 'via-bangladesh-new-no2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Checkpoint Mail | Created: 2024-2025 | Chưa dính IP Việt',
                'price' => 60000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Bangladesh'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],

            // 7. Via Ngoại Live Ads (2FA)
            [
                'category_slug' => 'via-ngoai-live-ads-2fa',
                'name' => 'Via France (2FA)',
                'slug' => 'via-france-2fa',
                'description' => 'Friend: 30-1000 | LIVE ADS | Created: 2025-2026 | Chưa dính IP Việt | Checkpoint Mail',
                'price' => 260000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Pháp (France)'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-2fa',
                'name' => 'Via Switzerland Cổ (2FA)',
                'slug' => 'via-switzerland-co-2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt',
                'price' => 191000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Thụy Sĩ'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-2fa',
                'name' => 'Via United Kingdom Cổ (2FA)',
                'slug' => 'via-uk-co-2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt',
                'price' => 240000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Anh Quốc (UK)'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-2fa',
                'name' => 'Via Poland Cổ (2FA)',
                'slug' => 'via-poland-co-2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt',
                'price' => 191000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Ba Lan'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-2fa',
                'name' => 'Via Germany Cổ (2FA)',
                'slug' => 'via-germany-co-2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Created: 2008-2021 | Chưa dính IP Việt',
                'price' => 200000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Đức (Germany)'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-2fa',
                'name' => 'Via Indonesia Cổ (2FA)',
                'slug' => 'via-indonesia-co-2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt',
                'price' => 120000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Indonesia'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-2fa',
                'name' => 'Via Philippines Cổ (2FA)',
                'slug' => 'via-philippines-co-2fa-ads',
                'description' => 'Friend: 30-5000 | LIVE ADS | Created: 2008-2023 | Chưa dính IP Việt',
                'price' => 120000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Philippines'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'via-ngoai-live-ads-2fa',
                'name' => 'Via Egypt Cổ (2FA)',
                'slug' => 'via-egypt-co-2fa',
                'description' => 'Friend: 30-5000 | LIVE ADS | Created: 2008-2024 | Chưa dính IP Việt',
                'price' => 96000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Ai Cập'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],

            // 8. Clone Global
            [
                'category_slug' => 'clone-global',
                'name' => 'Clone Ngoại Random Die Ads V2',
                'slug' => 'clone-ngoai-random-die-ads-v2',
                'description' => 'Có Avatar + Cover + Info | Created: > 3 month | Very Hotmail | Đã Bật 2FA',
                'price' => 2500,
                'attributes' => [['label' => 'Loại', 'value' => 'Clone ngoại'], ['label' => 'Bảo mật', 'value' => 'Có 2FA'], ['label' => 'Tuổi', 'value' => '> 3 tháng']]
            ],
            [
                'category_slug' => 'clone-global',
                'name' => 'Clone United States',
                'slug' => 'clone-us-facebook',
                'description' => 'Có Avatar + Cover + Info | Created: > 5 day | Created Ads: > 5 day | Verify Hotmail | Đã bật 2FA',
                'price' => 7000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Mỹ (US)'], ['label' => 'Xác minh', 'value' => 'Verify Hotmail']]
            ],
            [
                'category_slug' => 'clone-global',
                'name' => 'Clone Canada',
                'slug' => 'clone-canada-fb',
                'description' => 'Có Avatar + Cover + Info | Created: > 2 month | Created Ads: > 2 month | Verify Hotmail | Đã bật 2FA',
                'price' => 10000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Canada'], ['label' => 'Tuổi', 'value' => '> 2 tháng']]
            ],

            // 9. Clone Việt Nam
            [
                'category_slug' => 'clone-viet-nam',
                'name' => 'Clone Việt Nam Verify Hotmail',
                'slug' => 'clone-vn-verify-hotmail',
                'description' => 'Có Avatar + Cover + Info | Ngày tạo: > 3 month | Đã bật 2FA | Very Hotmail',
                'price' => 7000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Việt Nam'], ['label' => 'Verify', 'value' => 'Hotmail'], ['label' => 'Bảo mật', 'value' => '2FA']]
            ],
            [
                'category_slug' => 'clone-viet-nam',
                'name' => 'Clone Việt Nam Verify Gmail (No2FA)',
                'slug' => 'clone-vn-verify-gmail-no2fa',
                'description' => 'Có Avatar + Cover + Info | Ngày tạo: > 2 month | Chưa bật 2FA | Very Gmail | Friend: 0-20',
                'price' => 6000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'Việt Nam'], ['label' => 'Verify', 'value' => 'Gmail'], ['label' => 'Bảo mật', 'value' => 'No2FA']]
            ],

            // 10. Via Ngoại DIE ADS
            [
                'category_slug' => 'via-ngoai-die-ads',
                'name' => 'Via United States Cổ DIE ADS',
                'slug' => 'via-us-co-die-ads',
                'description' => 'Thích hợp Seeding, Spy | DIE ADS | Friend : 30-5000 | Created: 2010-2021',
                'price' => 239000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'US (Mỹ)'], ['label' => 'TKQC', 'value' => 'Die Ads']]
            ],
            [
                'category_slug' => 'via-ngoai-die-ads',
                'name' => 'Via Ngoại Cổ DIE ADS 3-5K Friend',
                'slug' => 'via-ngoai-co-die-ads-3-5k-friend',
                'description' => 'Thích hợp Seeding | DIE ADS | Friend : 3000-5000 | Năm tạo: 2008-2024',
                'price' => 156000,
                'attributes' => [['label' => 'Friend', 'value' => '3K - 5K'], ['label' => 'TKQC', 'value' => 'Die Ads']]
            ],
            [
                'category_slug' => 'via-ngoai-die-ads',
                'name' => 'Via Ngoại New Die Ads',
                'slug' => 'via-ngoai-new-die-ads',
                'description' => 'Thích hợp Seeding | DIE ADS | Friend : 30-5000 | Checkpoint Mail | Created: 2024-2025',
                'price' => 24000,
                'attributes' => [['label' => 'Loại', 'value' => 'Via Ngoại New'], ['label' => 'TKQC', 'value' => 'Die Ads']]
            ],

            // 11. Hotmail Live V.V
            [
                'category_slug' => 'hotmail-live-vv',
                'name' => 'Outlook Trusted [GRAPH API]',
                'slug' => 'outlook-trusted-graph-api',
                'description' => 'Live for 6-12 months | Không cần very SĐT | Recommend to use Graph API for access',
                'price' => 400,
                'attributes' => [['label' => 'Hạn dùng', 'value' => '6-12 tháng'], ['label' => 'Yêu cầu', 'value' => 'Graph API']]
            ],
            [
                'category_slug' => 'hotmail-live-vv',
                'name' => 'Hotmail Trusted [GRAPH API]',
                'slug' => 'hotmail-trusted-graph-api',
                'description' => 'Live for 6-12 months | Không cần very SĐT | Recommend to use Graph API for access',
                'price' => 400,
                'attributes' => [['label' => 'Hạn dùng', 'value' => '6-12 tháng'], ['label' => 'API', 'value' => 'Graph API']]
            ],

            // 12. Clone Instagram
            [
                'category_slug' => 'clone-instagram',
                'name' => 'Clone Instagram US đã qua reg BM',
                'slug' => 'clone-instagram-us-reg-bm',
                'description' => 'Reg Android | Verify Phone | Đã Bật 2FA | Name US | Created: > 2 month | Phù hợp spam, fishing...',
                'price' => 2000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'US (Mỹ)'], ['label' => 'Đã Reg', 'value' => 'BM Facebook']]
            ],
            [
                'category_slug' => 'clone-instagram',
                'name' => 'Clone Instagram US',
                'slug' => 'clone-instagram-us-basic',
                'description' => 'Reg Android | Verify Phone | Đã Bật 2FA | Name US | Created: > 15 day',
                'price' => 3000,
                'attributes' => [['label' => 'Quốc gia', 'value' => 'US (Mỹ)'], ['label' => 'Tuổi', 'value' => '> 15 ngày']]
            ]
        ];

        $products = [];
        foreach ($productsData as $data) {
            $catSlug = $data['category_slug'];
            $catId = isset($categoriesMap[$catSlug]) ? $categoriesMap[$catSlug]->id : null;
            
            // Xóa category_slug trước khi insert DB
            unset($data['category_slug']);
            
            $products[] = ProductModel::create(array_merge($data, [
                'category_id' => $catId,
                'status' => 'active'
            ]));
        }

        // 4. Tạo Tồn kho mẫu (Inventory Items) dồi dào - Mỗi sản phẩm 15 items => Tổng hơn 760 items tồn kho
        foreach ($products as $p) {
            $template = "1000" . rand(100000000, 999999999) . "{i}|pass_secret_via_{i}|2FA_SECRET_KEY_XYZ_{i}|recovery_mail_{i}@gmail.com|pass_mail";
            if (str_contains($p->slug, 'gmail') || str_contains($p->slug, 'outlook') || str_contains($p->slug, 'hotmail')) {
                $template = "mail_seed_account_{i}@hotmail.com|pass_mail_{i}|recovery_seed_mail_{i}@outlook.com";
            } elseif (str_contains($p->slug, 'proxy')) {
                $template = "45.122.134.{i}:8080:proxy_user_{i}:proxy_pass_{i}";
            } elseif (str_contains($p->slug, 'instagram')) {
                $template = "ig_user_{i}|pass_ig_{i}|2FA_SECRET_XYZ_{i}|mail_ig_{i}@example.com";
            }
            
            for ($i = 1; $i <= 15; $i++) {
                $rawText = str_replace('{i}', $i, $template);
                InventoryItemModel::create([
                    'product_id' => $p->id,
                    'data'       => Crypt::encryptString($rawText),
                    'status'     => 'available'
                ]);
            }
        }

        // 5. Sinh ngẫu nhiên 50 Đơn hàng mẫu để hiển thị biểu đồ và lịch sử phân trang cực kỳ phong phú
        $statusDistribution = array_merge(
            array_fill(0, 75, 'paid'),
            array_fill(0, 15, 'pending'),
            array_fill(0, 10, 'cancelled')
        );

        for ($orderIdx = 1; $orderIdx <= 50; $orderIdx++) {
            // Chọn ngẫu nhiên khách hàng
            $customer = $customers[array_rand($customers)];
            
            // Ngày đặt hàng ngẫu nhiên trong vòng 30 ngày qua
            $daysAgo = rand(0, 30);
            $orderDate = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            // Chọn ngẫu nhiên trạng thái
            $status = $statusDistribution[array_rand($statusDistribution)];
            
            // Tạo mã đơn hàng độc nhất
            $orderCode = 'ORD-' . $orderDate->format('Ymd') . '-' . strtoupper(Str::random(6));

            // Chọn ngẫu nhiên từ 1 đến 2 sản phẩm khác nhau cho đơn hàng này
            $numItems = rand(1, 2);
            $selectedProducts = (array) array_rand($products, $numItems);
            if (!is_array($selectedProducts)) {
                $selectedProducts = [$selectedProducts];
            }

            $orderItems = [];
            $totalPrice = 0;

            foreach ($selectedProducts as $prodIdx) {
                $p = $products[$prodIdx];
                $quantity = rand(1, 2);
                
                $orderItems[] = [
                    'product' => $p,
                    'price' => $p->price,
                    'quantity' => $quantity
                ];
                $totalPrice += $p->price * $quantity;
            }

            // Tạo đơn hàng trước
            $order = OrderModel::create([
                'user_id'     => $customer->id,
                'order_code'  => $orderCode,
                'total_price' => $totalPrice,
                'status'      => $status,
                'created_at'  => $orderDate,
                'updated_at'  => $orderDate
            ]);

            // Tạo các order items và cập nhật trạng thái kho khớp chuẩn
            foreach ($orderItems as $item) {
                $p = $item['product'];
                $qty = $item['quantity'];

                OrderItemModel::create([
                    'order_id'     => $order->id,
                    'product_id'   => $p->id,
                    'product_name' => $p->name,
                    'price'        => $item['price'],
                    'quantity'     => $qty,
                    'created_at'   => $orderDate,
                    'updated_at'   => $orderDate
                ]);

                // Nếu đơn hàng 'paid' hoặc 'pending' -> Trích xuất tài nguyên tương ứng từ kho
                if ($status === 'paid') {
                    $itemsToSell = InventoryItemModel::where('product_id', $p->id)
                        ->where('status', 'available')
                        ->limit($qty)
                        ->get();

                    foreach ($itemsToSell as $inventoryItem) {
                        $inventoryItem->update([
                            'status' => 'sold',
                            'order_id' => $order->id,
                            'updated_at' => $orderDate
                        ]);
                    }
                } elseif ($status === 'pending') {
                    $itemsToReserve = InventoryItemModel::where('product_id', $p->id)
                        ->where('status', 'available')
                        ->limit($qty)
                        ->get();

                    foreach ($itemsToReserve as $inventoryItem) {
                        $inventoryItem->update([
                            'status' => 'reserved',
                            'order_id' => $order->id,
                            'updated_at' => $orderDate
                        ]);
                    }
                }
            }
        }
    }
}
