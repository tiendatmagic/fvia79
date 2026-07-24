<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\User;
use App\Infrastructure\Persistence\Eloquent\UserModel;

class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        return new User(
            $model->id,
            $model->username ?? '',
            $model->name,
            $model->email,
            $model->password,
            $model->role,
            (float) $model->balance,
            $model->refresh_token_hash,
            $model->refresh_token_expired_at ? new \DateTime($model->refresh_token_expired_at->toDateTimeString()) : null,
            $model->phone,
            $model->google2fa_secret,
            $model->google2fa_enabled,
            $model->api_key,
            $model->status ?? 'active',
            $model->uid,
            $model->telegram_chat_id,
            (bool) ($model->telegram_notifications_enabled ?? true)
        );
    }
 
    public static function toPersistence(User $entity): UserModel
    {
        $model = new UserModel();
        if ($entity->getId()) {
            $model = UserModel::find($entity->getId()) ?: $model;
        }

        if ($entity->getUid()) {
            $model->uid = $entity->getUid();
        }
 
        $model->username = $entity->getUsername();
        $model->name = $entity->getName();
        $model->email = $entity->getEmail();
        $model->phone = $entity->getPhone();
        $model->password = $entity->getPassword();
        $model->role = $entity->getRole();
        $model->balance = $entity->getBalance();
        $model->refresh_token_hash = $entity->getRefreshTokenHash();
        $model->refresh_token_expired_at = $entity->getRefreshTokenExpiredAt();
        $model->google2fa_secret = $entity->getGoogle2faSecret();
        $model->google2fa_enabled = $entity->isGoogle2faEnabled();
        $model->api_key = $entity->getApiKey();
        $model->status = $entity->getStatus();
        $model->telegram_chat_id = $entity->getTelegramChatId();
        $model->telegram_notifications_enabled = $entity->isTelegramNotificationsEnabled();
 
        return $model;
    }
}
