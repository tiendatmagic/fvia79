<?php

namespace App\Http\Controllers;

use App\Application\Services\TelegramNotificationService;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly TelegramNotificationService $telegramService
    ) {}

    public function handleWebhook(Request $request): JsonResponse
    {
        $update = $request->all();
        Log::info('Telegram Webhook payload: ', $update);

        $message = $update['message'] ?? $update['edited_message'] ?? null;
        if (!$message) {
            return response()->json(['status' => 'ignored']);
        }

        $chatId = (string) ($message['chat']['id'] ?? '');
        $text = trim($message['text'] ?? '');

        if (empty($chatId) || empty($text)) {
            return response()->json(['status' => 'ignored']);
        }

        if (str_starts_with($text, '/start')) {
            $parts = explode(' ', $text);
            $uidParam = trim($parts[1] ?? '');

            if (!empty($uidParam)) {
                $user = UserModel::where('uid', $uidParam)
                    ->orWhere('id', $uidParam)
                    ->first();

                if ($user) {
                    $user->telegram_chat_id = $chatId;
                    $user->telegram_notifications_enabled = true;
                    $user->save();

                    $replyText = "🎉 <b>Kết nối tài khoản thành công!</b>\n\n"
                        . "👤 <b>Tài khoản:</b> {$user->name}\n"
                        . "🆔 <b>UID:</b> <code>{$user->uid}</code>\n\n"
                        . "Bạn đã sẵn sàng nhận thông báo tự động khi có <b>Hàng mới về kho</b> và <b>Đơn hàng vừa mua</b> 24/7 từ hệ thống ISOCALMARKET.";

                    $this->telegramService->sendMessage($chatId, $replyText);

                    return response()->json(['status' => 'connected', 'uid' => $user->uid]);
                }
            }

            $replyText = "👋 <b>Chào mừng bạn đến với Bot Thông Báo ISOCALMARKET!</b>\n\n"
                . "Để tự động kết nối nhận thông báo số dư & đơn hàng, vui lòng truy cập trang <b>Hồ sơ cá nhân</b> trên website và bấm nút <b>Kết nối Telegram</b>, hoặc gửi tin nhắn theo cú pháp:\n\n"
                . "<code>/start UID_của_bạn</code>\n\n"
                . "Ví dụ: <code>/start 10000005</code>";

            $this->telegramService->sendMessage($chatId, $replyText);
        }

        return response()->json(['status' => 'ok']);
    }
}
