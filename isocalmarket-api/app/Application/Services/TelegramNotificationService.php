<?php

namespace App\Application\Services;

use App\Domain\Entities\InventoryBatch;
use App\Domain\Entities\Order;
use App\Domain\Entities\Product;
use App\Infrastructure\Persistence\Eloquent\SystemSettingModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    public function getBotToken(): ?string
    {
        $setting = SystemSettingModel::where('key', 'telegram_bot_token')->first();
        return $setting ? $setting->value : null;
    }

    public function getBotUsername(): string
    {
        $setting = SystemSettingModel::where('key', 'telegram_bot_username')->first();
        return $setting && !empty($setting->value) ? ltrim($setting->value, '@') : 'isocalmarketnoti_bot';
    }

    public function isEnabled(): bool
    {
        $enabledSetting = SystemSettingModel::where('key', 'telegram_bot_enabled')->first();
        $token = $this->getBotToken();

        return !empty($token) && ($enabledSetting === null || in_array((string) $enabledSetting->value, ['1', 'true', 'active'], true));
    }

    public function testBotConnection(?string $overrideToken = null): array
    {
        $token = $overrideToken ?: $this->getBotToken();

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Telegram Bot Token is not configured.',
            ];
        }

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");

            if ($response->successful() && $response->json('ok') === true) {
                $result = $response->json('result');
                return [
                    'success' => true,
                    'message' => 'Connected to Telegram Bot successfully.',
                    'bot' => [
                        'id' => $result['id'] ?? null,
                        'first_name' => $result['first_name'] ?? '',
                        'username' => $result['username'] ?? '',
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'Telegram API error: ' . ($response->json('description') ?? 'Invalid token or HTTP error.'),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect to Telegram API: ' . $e->getMessage(),
            ];
        }
    }

    public function sendMessage(string $chatId, string $text, ?string $overrideToken = null): bool
    {
        $token = $overrideToken ?: $this->getBotToken();

        if (empty($token) || empty($chatId)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false,
            ]);

            if (!$response->successful()) {
                Log::warning("Telegram sendMessage failed for chat {$chatId}: " . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("Telegram notification exception for chat {$chatId}: " . $e->getMessage());
            return false;
        }
    }

    public function notifyNewInventoryBatch(Product $product, InventoryBatch $batch): int
    {
        if (!$this->isEnabled()) {
            return 0;
        }

        $stockAdded = $batch->getInitialCount();
        if ($stockAdded <= 0) {
            return 0;
        }

        $productId = $product->getId();
        $sixMonthsAgo = now()->subMonths(6);

        // Chỉ lấy user đã mua đúng sản phẩm này trong 6 tháng gần nhất,
        // đã kết nối Telegram và đang bật nhận thông báo.
        $users = UserModel::whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->where('telegram_notifications_enabled', true)
            ->whereHas('orders', function ($q) use ($productId, $sixMonthsAgo) {
                $q->where('created_at', '>=', $sixMonthsAgo)
                  ->whereHas('items', function ($q2) use ($productId) {
                      $q2->where('product_id', $productId);
                  });
            })
            ->get();

        if ($users->isEmpty()) {
            return 0;
        }

        $productNameVi = $product->getLocalizedName('vi') ?: $product->getName();
        $priceVnd = number_format($product->getPrice(), 0, ',', '.') . ' VNĐ';

        $message = "📦 <b>[HÀNG MỚI VỀ KHO]</b>\n\n"
            . "📌 <b>Sản phẩm:</b> {$productNameVi}\n"
            . "📥 <b>Số lượng mới nhập:</b> {$stockAdded} sản phẩm\n"
            . "💰 <b>Đơn giá:</b> {$priceVnd}\n\n"
            . "🛒 Mua ngay tại hệ thống ISOCALMARKET!";

        $sentCount = 0;
        foreach ($users as $user) {
            if ($this->sendMessage($user->telegram_chat_id, $message)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    public function notifyOrderSuccess(Order $order, UserModel $user): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (empty($user->telegram_chat_id) || !$user->telegram_notifications_enabled) {
            return false;
        }

        $totalUsd = number_format($order->getTotalPrice(), 2);
        $orderCode = $order->getOrderCode();

        $itemsSummary = '';
        foreach ($order->getItems() as $item) {
            $prodName = $item->getProductName();
            $qty = $item->getQuantity();
            $priceUsd = number_format($item->getPrice(), 2);
            $itemsSummary .= "• <b>{$prodName}</b> x {$qty} (<b>\${$priceUsd}</b>/sp)\n";
        }

        $message = "🎉 <b>[ĐƠN HÀNG MUA THÀNH CÔNG]</b>\n\n"
            . "📋 <b>Mã đơn hàng:</b> <code>#{$orderCode}</code>\n"
            . "💵 <b>Tổng thanh toán:</b> \${$totalUsd}\n\n"
            . "🛍️ <b>Danh sách sản phẩm:</b>\n"
            . $itemsSummary . "\n"
            . "⚠️ <i>Ghi chú: Vì lý do bảo mật, dữ liệu tài nguyên không gửi qua Telegram. Vui lòng xem chi tiết tại Lịch sử đơn hàng trên website.</i>\n\n"
            . "Cảm ơn bạn đã tin tưởng dịch vụ của ISOCALMARKET!";

        return $this->sendMessage($user->telegram_chat_id, $message);
    }
}
