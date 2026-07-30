<?php

namespace App\Http\Controllers;

use App\Application\Services\TelegramLinkService;
use App\Application\Services\TelegramNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly TelegramNotificationService $telegramService,
        private readonly TelegramLinkService $telegramLinkService
    ) {}

    public function handleWebhook(Request $request): JsonResponse
    {
        $expectedSecret = (string) config('services.telegram.webhook_secret', '');
        if (strlen($expectedSecret) < 32
            || strlen($expectedSecret) > 256
            || ! preg_match('/^[A-Za-z0-9_-]+$/', $expectedSecret)) {
            Log::error('Telegram webhook rejected because TELEGRAM_WEBHOOK_SECRET is not configured.');

            return response()->json(['status' => 'unavailable'], 503);
        }

        $providedSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');
        if ($providedSecret === '' || ! hash_equals($expectedSecret, $providedSecret)) {
            return response()->json(['status' => 'forbidden'], 403);
        }

        $update = $request->all();
        $message = $update['message'] ?? null;
        if (! is_array($message)) {
            return response()->json(['status' => 'ignored']);
        }

        $chatId = (string) ($message['chat']['id'] ?? '');
        $senderId = (string) ($message['from']['id'] ?? '');
        $chatType = (string) ($message['chat']['type'] ?? '');
        $text = trim((string) ($message['text'] ?? ''));

        if ($chatId === '' || $senderId !== $chatId || $chatType !== 'private' || $text === '') {
            return response()->json(['status' => 'ignored']);
        }

        if (! preg_match('/^\/start(?:@\w+)?(?:\s+(\S+))?$/', $text, $matches)) {
            return response()->json(['status' => 'ok']);
        }

        $plainToken = trim((string) ($matches[1] ?? ''));
        $result = $plainToken !== ''
            ? $this->telegramLinkService->consumeToken($plainToken, $chatId)
            : ['status' => 'invalid'];

        if ($result['status'] === 'connected') {
            $this->telegramService->sendMessage(
                $chatId,
                "🎉 <b>Account Linked Successfully!</b>\n\n"
                ."You can now receive inventory and successful order notifications from FVIA79."
            );

            return response()->json(['status' => 'connected']);
        }

        $messageByStatus = match ($result['status']) {
            'already_connected' => 'This website account is already connected to another Telegram account. Disconnect it on the website first.',
            'chat_in_use' => 'This Telegram account is already connected to another website account.',
            default => 'This connection link is invalid, expired, or already used. Return to your profile and create a new link.',
        };
        $this->telegramService->sendMessage($chatId, "⚠️ <b>Unable to link account</b>\n\n".$messageByStatus);

        return response()->json(['status' => 'rejected']);
    }
}
