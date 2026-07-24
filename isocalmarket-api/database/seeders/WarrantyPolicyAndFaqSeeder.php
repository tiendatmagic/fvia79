<?php

namespace Database\Seeders;

use App\Models\FaqModel;
use App\Models\PolicyCategoryModel;
use App\Models\WarrantyPolicyModel;
use Illuminate\Database\Seeder;

class WarrantyPolicyAndFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (PolicyCategoryModel::count() === 0) {
            PolicyCategoryModel::create([
                'key' => 'bm',
                'name_vi' => 'Business Manager (BM)',
                'name_en' => 'Business Manager (BM)',
                'icon' => 'package',
                'sort_order' => 10,
                'is_active' => true,
            ]);

            PolicyCategoryModel::create([
                'key' => 'via',
                'name_vi' => 'Via / Clone Facebook',
                'name_en' => 'Via / Clone Facebook',
                'icon' => 'customers',
                'sort_order' => 20,
                'is_active' => true,
            ]);
        }
        if (WarrantyPolicyModel::count() === 0) {
            $policies = [
                // BM Accepted
                [
                    'category' => 'bm',
                    'type' => 'accepted',
                    'content_vi' => 'BM bị lỗi hoặc Không nhận được link truy cập ngay sau khi bàn giao',
                    'content_en' => 'BM error or failed to receive access link right after delivery',
                    'tag_vi' => '24 giờ',
                    'tag_en' => '24 hours',
                    'tag_color' => 'blue',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'category' => 'bm',
                    'type' => 'accepted',
                    'content_vi' => 'BM bị chết Trước khi KHÁCH HÀNG sử dụng',
                    'content_en' => 'BM dead BEFORE customer usage',
                    'tag_vi' => '24 giờ',
                    'tag_en' => '24 hours',
                    'tag_color' => 'blue',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'category' => 'bm',
                    'type' => 'accepted',
                    'content_vi' => 'BM đã xác minh doanh nghiệp (Business Verification)',
                    'content_en' => 'Business Verified BM lost verification status',
                    'tag_vi' => '07 ngày',
                    'tag_en' => '07 days',
                    'tag_color' => 'emerald',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
                [
                    'category' => 'bm',
                    'type' => 'accepted',
                    'content_vi' => 'Giới hạn chi tiêu hàng ngày bị giảm — áp dụng cho BM có ghi rõ hoặc thoả thuận ở mô tả sản phẩm',
                    'content_en' => 'Daily spending limit dropped (applicable for BMs explicitly specified in description)',
                    'tag_vi' => '07 ngày',
                    'tag_en' => '07 days',
                    'tag_color' => 'emerald',
                    'sort_order' => 4,
                    'is_active' => true,
                ],

                // BM Rejected
                [
                    'category' => 'bm',
                    'type' => 'rejected',
                    'content_vi' => 'BM bị vô hiệu hóa / die sau khi khách đã lên camp quảng cáo hoặc add thẻ thanh toán',
                    'content_en' => 'BM disabled / dead after creating campaigns or adding payment cards',
                    'tag_vi' => 'Từ chối',
                    'tag_en' => 'Rejected',
                    'tag_color' => 'rose',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'category' => 'bm',
                    'type' => 'rejected',
                    'content_vi' => 'Quá thời hạn bảo hành quy định (24 giờ đối với BM thường, 07 ngày đối với BM xác minh)',
                    'content_en' => 'Exceeded warranty time limit (24h for normal BM, 07 days for Verified BM)',
                    'tag_vi' => 'Hết hạn',
                    'tag_en' => 'Expired',
                    'tag_color' => 'amber',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'category' => 'bm',
                    'type' => 'rejected',
                    'content_vi' => 'Khách hàng tự ý kích out QTV gốc hoặc thay đổi thông tin BM khi chưa kiểm tra thành công',
                    'content_en' => 'Customer removed original admin or changed BM info before verification',
                    'tag_vi' => 'Từ chối',
                    'tag_en' => 'Rejected',
                    'tag_color' => 'rose',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
                [
                    'category' => 'bm',
                    'type' => 'rejected',
                    'content_vi' => 'BM bị tụt giới hạn chi tiêu do chạy quảng cáo vi phạm chính sách Facebook hoặc bùng tiền',
                    'content_en' => 'Daily limit reduced due to policy violation ads or unpaid balance',
                    'tag_vi' => 'Vi phạm',
                    'tag_en' => 'Violation',
                    'tag_color' => 'rose',
                    'sort_order' => 4,
                    'is_active' => true,
                ],

                // Via Accepted
                [
                    'category' => 'via',
                    'type' => 'accepted',
                    'content_vi' => 'Login lần đầu thất bại: sai UID, sai Mật khẩu, sai mã 2FA, checkpoint 282 sẵn',
                    'content_en' => 'First login failure: wrong UID, wrong Password, wrong 2FA, pre-existing 282 checkpoint',
                    'tag_vi' => 'Lần đầu',
                    'tag_en' => 'First login',
                    'tag_color' => 'amber',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'category' => 'via',
                    'type' => 'accepted',
                    'content_vi' => 'Via mô tả "Checkpoint Mail" — bảo hành checkpoint 956 kể cả sau khi khách đã sử dụng',
                    'content_en' => 'Via with "Mail Checkpoint" description — 956 checkpoint warranty after usage',
                    'sub_content_vi' => '(Yêu cầu: chưa thay đổi email, Chưa bật 2FA)',
                    'sub_content_en' => '(Requirement: email unchanged, 2FA not enabled)',
                    'tag_vi' => '07 ngày',
                    'tag_en' => '07 days',
                    'tag_color' => 'emerald',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'category' => 'via',
                    'type' => 'accepted',
                    'content_vi' => 'Tài khoản Facebook người dùng thật/Clone bị vô hiệu hóa tài khoản cá nhân, tài khoản trả Trước hoặc hạn chế quảng cáo từ Trước thời điểm mua',
                    'content_en' => 'Personal account disabled or ad restricted prior to purchase time',
                    'tag_vi' => 'Có sẵn',
                    'tag_en' => 'Pre-existing',
                    'tag_color' => 'amber',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
                [
                    'category' => 'via',
                    'type' => 'accepted',
                    'content_vi' => 'Thông tin Via Không đúng với mô tả khi mua — vui lòng Không sử dụng để được bảo hành',
                    'content_en' => 'Via specs mismatched with description — please do not use to claim warranty',
                    'tag_vi' => 'Không dùng',
                    'tag_en' => 'Do not use',
                    'tag_color' => 'rose',
                    'sort_order' => 4,
                    'is_active' => true,
                ],

                // Via Rejected
                [
                    'category' => 'via',
                    'type' => 'rejected',
                    'content_vi' => 'Tài khoản dính checkpoint / die sau khi khách đã thay đổi Mật khẩu, Email hoặc Mã 2FA',
                    'content_en' => 'Account checkpointed / dead after password, email, or 2FA changed',
                    'tag_vi' => 'Từ chối',
                    'tag_en' => 'Rejected',
                    'tag_color' => 'rose',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'category' => 'via',
                    'type' => 'rejected',
                    'content_vi' => 'Quá thời hạn bảo hành mua hàng hiển thị tại mục Lịch Sử Mua Hàng',
                    'content_en' => 'Exceeded purchase warranty duration shown in Order History',
                    'tag_vi' => 'Hết hạn',
                    'tag_en' => 'Expired',
                    'tag_color' => 'amber',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'category' => 'via',
                    'type' => 'rejected',
                    'content_vi' => 'Đăng nhập trên thiết bị bẩn, IP đen / Proxy rác bị Facebook quét checkpoint hàng loạt',
                    'content_en' => 'LoggedIn on dirty device, blacklisted IP / poor proxy causing mass checkpoint',
                    'tag_vi' => 'Từ chối',
                    'tag_en' => 'Rejected',
                    'tag_color' => 'rose',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
                [
                    'category' => 'via',
                    'type' => 'rejected',
                    'content_vi' => 'Sử dụng tài khoản vào các mục đích vi phạm pháp luật (cờ bạc, lừa đảo, spam độc hại)',
                    'content_en' => 'Account used for illegal activities (gambling, scamming, harmful spam)',
                    'tag_vi' => 'Vi phạm',
                    'tag_en' => 'Violation',
                    'tag_color' => 'rose',
                    'sort_order' => 4,
                    'is_active' => true,
                ],
            ];

            foreach ($policies as $policy) {
                WarrantyPolicyModel::create($policy);
            }
        }

        if (FaqModel::count() === 0) {
            $faqs = [
                [
                    'question_vi' => 'Thời gian tiếp nhận và xử lý yêu cầu bảo hành là bao lâu?',
                    'question_en' => 'How long does it take to process a warranty request?',
                    'answer_vi' => 'Thời gian xử lý bảo hành thông thường từ 15 phút đến tối đa 24 giờ làm việc. Kỹ thuật viên sẽ kiểm tra tài khoản theo đúng khung giờ và điều kiện bảo hành.',
                    'answer_en' => 'Standard warranty processing time ranges from 15 minutes up to 24 business hours. Technicians check accounts strictly based on conditions.',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'question_vi' => 'Làm thế nào để gửi yêu cầu bảo hành đơn hàng đã mua?',
                    'question_en' => 'How can I submit a warranty claim for a purchased order?',
                    'answer_vi' => 'Quý khách vui lòng truy cập trang "Lịch sử đơn hàng", bấm vào đơn hàng tương ứng và nhấn nút "Yêu cầu bảo hành" hoặc liên hệ trực tiếp với bộ phận Chăm sóc khách hàng qua Telegram/Zalo kèm Mã đơn hàng.',
                    'answer_en' => 'Please visit "Order History", select your order and click "Request Warranty" or contact CS directly via Telegram/Zalo with your Order ID.',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'question_vi' => 'Trường hợp nào tài khoản sẽ bị từ chối bảo hành?',
                    'question_en' => 'In what scenarios will a warranty claim be rejected?',
                    'answer_vi' => 'Tài khoản bị từ chối bảo hành khi: Quá thời hạn bảo hành quy định, đã thay đổi thông tin (Email/Mật khẩu/2FA) đối với các loại tài khoản chưa được phép, sử dụng IP bẩn/bị đen, vi phạm chính sách của Facebook sau khi đã nhận bàn giao thành công.',
                    'answer_en' => 'Warranty is rejected if: exceeded time limit, unauthorized info modification (Email/Password/2FA), dirty/blacklisted IP usage, or Facebook policy violations after delivery.',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
                [
                    'question_vi' => 'Chính sách bảo hành BM xác minh doanh nghiệp (Business Verification)?',
                    'question_en' => 'What is the policy for Business Verified BMs?',
                    'answer_vi' => 'BM xác minh doanh nghiệp được bảo hành 07 ngày đối với các lỗi liên quan đến trạng thái xác minh bị mất hoặc link gia nhập không hoạt động. Không bảo hành trường hợp BM chết do lên camp vi phạm chính sách hoặc add thẻ bẩn.',
                    'answer_en' => 'Verified BMs have a 07-day warranty for lost verification status or broken join links. No warranty for dead BM caused by policy-violating ads or bad payment cards.',
                    'sort_order' => 4,
                    'is_active' => true,
                ],
            ];

            foreach ($faqs as $faq) {
                FaqModel::create($faq);
            }
        }
    }
}
