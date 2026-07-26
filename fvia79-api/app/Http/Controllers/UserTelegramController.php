<?php

namespace App\Http\Controllers;

use App\Application\Services\TelegramNotificationService;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserTelegramController extends Controller
{
    public function __construct(
        private readonly TelegramNotificationService $telegramService
    ) {}

    public function getStatus(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = UserModel::find($request->user()->id) ?? $request->user();
        $botUsername = $this->telegramService->getBotUsername();

        return response()->json([
            'status' => 'success',
            'data' => [
                'telegram_chat_id' => $user->telegram_chat_id,
                'telegram_notifications_enabled' => (bool) $user->telegram_notifications_enabled,
                'is_connected' => !empty($user->telegram_chat_id),
                'bot_username' => $botUsername,
                'connect_url' => "https://t.me/{$botUsername}?start={$user->uid}",
            ],
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = UserModel::find($request->user()->id) ?? $request->user();

        $validated = $request->validate([
            'telegram_chat_id' => 'nullable|string|max:100',
            'telegram_notifications_enabled' => 'nullable|boolean',
        ]);

        if (array_key_exists('telegram_chat_id', $validated)) {
            $user->telegram_chat_id = !empty($validated['telegram_chat_id']) ? trim($validated['telegram_chat_id']) : null;
        }

        if (array_key_exists('telegram_notifications_enabled', $validated)) {
            $user->telegram_notifications_enabled = (bool) $validated['telegram_notifications_enabled'];
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Telegram notification settings updated successfully.',
            'data' => [
                'telegram_chat_id' => $user->telegram_chat_id,
                'telegram_notifications_enabled' => (bool) $user->telegram_notifications_enabled,
                'is_connected' => !empty($user->telegram_chat_id),
            ],
        ]);
    }

    public function sendTestMessage(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = UserModel::find($request->user()->id) ?? $request->user();

        if (empty($user->telegram_chat_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please connect your Telegram Chat ID first.',
            ], 400);
        }

        $testText = "🔔 <b>[TEST NOTIFICATION]</b>\n\n"
            . "Hello <b>{$user->name}</b>,\n"
            . "FVIA79 system has successfully connected to your Telegram account.\n"
            . "You will receive automated notifications for <b>New Inventory</b> and <b>Successful Orders</b> 24/7!";

        $success = $this->telegramService->sendMessage($user->telegram_chat_id, $testText);

        if ($success) {
            return response()->json([
                'status' => 'success',
                'message' => 'Test message sent to your Telegram successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send test message. Please verify your Telegram Chat ID or make sure you have pressed /start on the bot.',
        ], 400);
    }

    public function disconnect(Request $request): JsonResponse
    {
        /** @var UserModel $user */
        $user = UserModel::find($request->user()->id) ?? $request->user();
        $user->telegram_chat_id = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Disconnected Telegram account successfully.',
        ]);
    }
}
