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

                    $replyText = "🎉 <b>Account Linked Successfully!</b>\n\n"
                        . "👤 <b>Account Name:</b> {$user->name}\n"
                        . "🆔 <b>UID:</b> <code>{$user->uid}</code>\n\n"
                        . "You are now ready to receive instant notifications for <b>New Inventory</b> and <b>Successful Orders</b> 24/7 from ISOCALMARKET.";

                    $this->telegramService->sendMessage($chatId, $replyText);

                    return response()->json(['status' => 'connected', 'uid' => $user->uid]);
                }
            }

            $replyText = "👋 <b>Welcome to ISOCALMARKET Notification Bot!</b>\n\n"
                . "To automatically link your account and receive instant updates, please visit your <b>User Profile</b> page on our website and click <b>Connect Telegram</b>, or send a message with the command:\n\n"
                . "<code>/start YOUR_UID</code>\n\n"
                . "Example: <code>/start 10000005</code>";

            $this->telegramService->sendMessage($chatId, $replyText);
        }

        return response()->json(['status' => 'ok']);
    }
}
