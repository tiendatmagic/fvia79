<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use App\Infrastructure\Persistence\Eloquent\OrderItemModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo Users
        $admin = UserModel::create([
            'name'     => 'admin',
            'email'    => env('SEED_ADMIN_EMAIL', 'admin@isocalmarket.com'),
            'password' => Hash::make(env('SEED_ADMIN_PASSWORD') ?? throw new \RuntimeException('SEED_ADMIN_PASSWORD must be set in .env before seeding.')),
            'role'     => 'admin'
        ]);

        $customer1 = UserModel::create([
            'name'     => 'Nguyễn Văn A',
            'email'    => env('SEED_CUSTOMER1_EMAIL', 'customer1@isocalmarket.com'),
            'password' => Hash::make(env('SEED_CUSTOMER_PASSWORD') ?? throw new \RuntimeException('SEED_CUSTOMER_PASSWORD must be set in .env before seeding.')),
            'role'     => 'customer'
        ]);

        $customer2 = UserModel::create([
            'name'     => 'Trần Thị B',
            'email'    => env('SEED_CUSTOMER2_EMAIL', 'customer2@isocalmarket.com'),
            'password' => Hash::make(env('SEED_CUSTOMER_PASSWORD') ?? throw new \RuntimeException('SEED_CUSTOMER_PASSWORD must be set in .env before seeding.')),
            'role'     => 'customer'
        ]);

        // 2. Tạo Products
        $p1 = ProductModel::create([
            'name'        => 'Facebook VIA US - Cổ 2018-2020',
            'slug'        => 'facebook-via-us-co-2018-2020',
            'description' => 'VIA US ngâm lâu năm, có sẵn 2FA, thích hợp chạy ads, bao back 7 ngày.',
            'price'       => 150000,
            'status'      => 'active',
            'attributes'  => [
                ['label' => 'Quốc gia', 'value' => 'US (Mỹ)'],
                ['label' => 'Loại', 'value' => 'Via Cổ (2018-2020)'],
                ['label' => 'Đặc tính', 'value' => 'Live Ads + Có 2FA']
            ]
        ]);

        $p2 = ProductModel::create([
            'name'        => 'Gmail Ngoại - Đã xác minh số điện thoại',
            'slug'        => 'gmail-ngoai-da-xac-minh-sdt',
            'description' => 'Gmail reg bằng IP US/EU, đã verify phone, ngâm trên 30 ngày.',
            'price'       => 5000,
            'status'      => 'active',
            'attributes'  => [
                ['label' => 'Quốc gia', 'value' => 'Ngoại (IP US/EU)'],
                ['label' => 'Đặc tính', 'value' => 'Đã verify phone (Ngâm > 30 ngày)']
            ]
        ]);

        $p3 = ProductModel::create([
            'name'        => 'Netflix Premium 1 Tháng - UHD',
            'slug'        => 'netflix-premium-1-thang-uhd',
            'description' => 'Tài khoản Netflix Premium xem phim Ultra HD chất lượng cao, hạn dùng 30 ngày.',
            'price'       => 80000,
            'status'      => 'active',
            'attributes'  => [
                ['label' => 'Thời hạn', 'value' => '30 ngày'],
                ['label' => 'Chất lượng', 'value' => 'Ultra HD (4K)']
            ]
        ]);

        $p4 = ProductModel::create([
            'name'        => 'ChatGPT Plus - Tài khoản dùng chung',
            'slug'        => 'chatgpt-plus-tai-khoan-dung-chung',
            'description' => 'Tài khoản ChatGPT Plus có sẵn GPT-4o, dùng chung 4 người, hạn dùng 30 ngày.',
            'price'       => 120000,
            'status'      => 'active',
            'attributes'  => [
                ['label' => 'Thời hạn', 'value' => '30 ngày'],
                ['label' => 'Phiên bản', 'value' => 'GPT-4o (Plus)']
            ]
        ]);

        $p5 = ProductModel::create([
            'name'        => 'Proxy IPv4 Private US - Sạch 100%',
            'slug'        => 'proxy-ipv4-private-us-sach-100',
            'description' => 'Proxy cá nhân IP US, tốc độ 100Mbps, không giới hạn băng thông.',
            'price'       => 45000,
            'status'      => 'active',
            'attributes'  => [
                ['label' => 'Quốc gia', 'value' => 'US (Mỹ)'],
                ['label' => 'Băng thông', 'value' => 'Không giới hạn (100Mbps)']
            ]
        ]);

        $p6 = ProductModel::create([
            'name'        => 'Via Kháng 902 & Chat Support',
            'slug'        => 'via-khang-902-chat-support',
            'description' => 'Tài khoản VIA đã kháng link 902 cực trâu, có sẵn nút Chat Support với Facebook để khiếu nại.',
            'price'       => 280000,
            'status'      => 'active',
            'attributes'  => [
                ['label' => 'Loại Via', 'value' => 'Kháng 902'],
                ['label' => 'Hỗ trợ', 'value' => 'Chat Support trực tiếp'],
                ['label' => 'Bảo hành', 'value' => 'Bao login, bao camp 24h']
            ]
        ]);

        $p7 = ProductModel::create([
            'name'        => 'Via Chuyên Set Camp',
            'slug'        => 'via-chuyen-set-camp',
            'description' => 'Tài khoản chuyên dùng để lên chiến dịch quảng cáo. Tỉ lệ tụt, hold, die hoặc need thấp.',
            'price'       => 180000,
            'status'      => 'active',
            'attributes'  => [
                ['label' => 'Đặc tính', 'value' => 'Chuyên Set Camp'],
                ['label' => 'Tỉ lệ lỗi', 'value' => 'Tụt, Hold, Die, Need cực thấp'],
                ['label' => 'Bảo hành', 'value' => 'Bao camp 24h']
            ]
        ]);

        // 3. Tạo Inventory Items (data được mã hóa)
        // Facebook VIA — 10 items
        for ($i = 1; $i <= 10; $i++) {
            InventoryItemModel::create([
                'product_id' => $p1->id,
                'data'       => Crypt::encryptString("10008394029482{$i}|password_fb_{$i}|2FA_SECRET_FB_XYZ{$i}|recovery_mail_{$i}@example.com"),
                'status'     => 'available'
            ]);
        }

        // Gmail — 15 items
        for ($i = 1; $i <= 15; $i++) {
            InventoryItemModel::create([
                'product_id' => $p2->id,
                'data'       => Crypt::encryptString("gmail_foreign_account_{$i}@gmail.com|pass_gmail_{$i}|recovery_mail_{$i}@outlook.com"),
                'status'     => 'available'
            ]);
        }

        // Netflix — 5 items
        for ($i = 1; $i <= 5; $i++) {
            InventoryItemModel::create([
                'product_id' => $p3->id,
                'data'       => Crypt::encryptString("netflix_shared_account_{$i}@netflix-premium.com|pass_netflix_{$i}|Profile_{$i}"),
                'status'     => 'available'
            ]);
        }

        // ChatGPT — 8 items
        for ($i = 1; $i <= 8; $i++) {
            InventoryItemModel::create([
                'product_id' => $p4->id,
                'data'       => Crypt::encryptString("chatgpt_account_plus_{$i}@chatgpt.com|pass_chatgpt_{$i}"),
                'status'     => 'available'
            ]);
        }

        // Proxy — 12 items
        for ($i = 1; $i <= 12; $i++) {
            InventoryItemModel::create([
                'product_id' => $p5->id,
                'data'       => Crypt::encryptString('45.122.134.' . (10 + $i) . ":8080:user_proxy_{$i}:pass_proxy_{$i}"),
                'status'     => 'available'
            ]);
        }

        // Via Kháng 902 — 10 items
        for ($i = 1; $i <= 10; $i++) {
            InventoryItemModel::create([
                'product_id' => $p6->id,
                'data'       => Crypt::encryptString("1000998822110{$i}|pass_via_902_{$i}|2FA_SECRET_902_XYZ{$i}|mail_902_{$i}@example.com|pass_mail_{$i}"),
                'status'     => 'available'
            ]);
        }

        // Via Chuyên Set Camp — 10 items
        for ($i = 1; $i <= 10; $i++) {
            InventoryItemModel::create([
                'product_id' => $p7->id,
                'data'       => Crypt::encryptString("1000776655440{$i}|pass_set_camp_{$i}|2FA_SECRET_CAMP_XYZ{$i}|mail_camp_{$i}@example.com|pass_mail_{$i}"),
                'status'     => 'available'
            ]);
        }

        // 4. Tạo các đơn hàng mẫu
        // Đơn hàng đã thanh toán của customer1
        $o1 = OrderModel::create([
            'user_id'     => $customer1->id,
            'order_code'  => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'total_price' => 305000, // 2 Facebook VIA + 1 Gmail
            'status'      => 'paid'
        ]);

        OrderItemModel::create([
            'order_id'     => $o1->id,
            'product_id'   => $p1->id,
            'product_name' => $p1->name,
            'price'        => 150000,
            'quantity'     => 2
        ]);

        OrderItemModel::create([
            'order_id'     => $o1->id,
            'product_id'   => $p2->id,
            'product_name' => $p2->name,
            'price'        => 5000,
            'quantity'     => 1
        ]);

        // Cập nhật inventory items tương ứng sang sold
        $itemsP1 = InventoryItemModel::where('product_id', $p1->id)->where('status', 'available')->limit(2)->get();
        foreach ($itemsP1 as $item) {
            $item->update(['status' => 'sold', 'order_id' => $o1->id]);
        }
        $itemP2 = InventoryItemModel::where('product_id', $p2->id)->where('status', 'available')->limit(1)->first();
        if ($itemP2) {
            $itemP2->update(['status' => 'sold', 'order_id' => $o1->id]);
        }

        // Đơn hàng đã thanh toán thứ 2 của customer1
        $o1b = OrderModel::create([
            'user_id'     => $customer1->id,
            'order_code'  => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'total_price' => 120000, // 1 ChatGPT Plus
            'status'      => 'paid'
        ]);

        OrderItemModel::create([
            'order_id'     => $o1b->id,
            'product_id'   => $p4->id,
            'product_name' => $p4->name,
            'price'        => 120000,
            'quantity'     => 1
        ]);

        $itemP4 = InventoryItemModel::where('product_id', $p4->id)->where('status', 'available')->first();
        if ($itemP4) {
            $itemP4->update(['status' => 'sold', 'order_id' => $o1b->id]);
        }

        // Đơn hàng pending của customer1
        $o2 = OrderModel::create([
            'user_id'     => $customer1->id,
            'order_code'  => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'total_price' => 45000, // 1 Proxy
            'status'      => 'pending'
        ]);

        OrderItemModel::create([
            'order_id'     => $o2->id,
            'product_id'   => $p5->id,
            'product_name' => $p5->name,
            'price'        => 45000,
            'quantity'     => 1
        ]);

        $itemP5 = InventoryItemModel::where('product_id', $p5->id)->where('status', 'available')->first();
        if ($itemP5) {
            $itemP5->update(['status' => 'reserved', 'order_id' => $o2->id]);
        }

        // Đơn hàng cancelled của customer2
        $o3 = OrderModel::create([
            'user_id'     => $customer2->id,
            'order_code'  => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'total_price' => 80000, // 1 Netflix
            'status'      => 'cancelled'
        ]);

        OrderItemModel::create([
            'order_id'     => $o3->id,
            'product_id'   => $p3->id,
            'product_name' => $p3->name,
            'price'        => 80000,
            'quantity'     => 1
        ]);
        // Cancelled — không giữ inventory item
    }
}
