<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Eloquent\TelegramLinkTokenModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TelegramLinkService
{
    private const TOKEN_TTL_MINUTES = 10;

    public function createToken(UserModel $user): array
    {
        if (! empty($user->telegram_chat_id)) {
            throw new \DomainException('Disconnect the current Telegram account before creating a new link.');
        }

        return DB::transaction(function () use ($user) {
            TelegramLinkTokenModel::where('user_id', $user->id)
                ->whereNull('used_at')
                ->delete();

            $plainToken = 'link_'.Str::random(48);
            $expiresAt = now()->addMinutes(self::TOKEN_TTL_MINUTES);

            TelegramLinkTokenModel::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $plainToken),
                'expires_at' => $expiresAt,
            ]);

            return [
                'token' => $plainToken,
                'expires_at' => $expiresAt,
            ];
        });
    }

    public function consumeToken(string $plainToken, string $chatId): array
    {
        if (! preg_match('/^link_[A-Za-z0-9]{48}$/', $plainToken)) {
            return ['status' => 'invalid'];
        }

        try {
            return DB::transaction(function () use ($plainToken, $chatId) {
                $linkToken = TelegramLinkTokenModel::where('token_hash', hash('sha256', $plainToken))
                    ->lockForUpdate()
                    ->first();

                if (! $linkToken || $linkToken->used_at !== null || $linkToken->expires_at->isPast()) {
                    return ['status' => 'invalid'];
                }

                $user = UserModel::whereKey($linkToken->user_id)->lockForUpdate()->first();
                if (! $user) {
                    return ['status' => 'invalid'];
                }

                if (! empty($user->telegram_chat_id) && (string) $user->telegram_chat_id !== $chatId) {
                    return ['status' => 'already_connected'];
                }

                $chatInUse = UserModel::where('telegram_chat_id', $chatId)
                    ->where('id', '!=', $user->id)
                    ->exists();
                if ($chatInUse) {
                    return ['status' => 'chat_in_use'];
                }

                $user->telegram_chat_id = $chatId;
                $user->telegram_notifications_enabled = true;
                $user->save();

                $linkToken->used_at = now();
                $linkToken->save();

                return ['status' => 'connected', 'user' => $user];
            });
        } catch (QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23505'], true)) {
                return ['status' => 'chat_in_use'];
            }

            throw $e;
        }
    }
}
