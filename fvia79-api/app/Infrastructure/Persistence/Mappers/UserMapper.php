<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\User;
use App\Infrastructure\Persistence\Eloquent\UserModel;

class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        $featureTiersEnabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);

        $tierArray = null;
        $tierId = $featureTiersEnabled ? $model->membership_tier_id : null;
        if ($featureTiersEnabled && $model->relationLoaded('membershipTier') && $model->membershipTier) {
            $tier = $model->membershipTier;
            $tierArray = [
                'id' => $tier->id,
                'level' => $tier->level,
                'name_vi' => $tier->name_vi,
                'name_en' => $tier->name_en,
                'min_deposit' => (float) $tier->min_deposit,
                'discount_percent' => (float) $tier->discount_percent,
                'icon' => $tier->icon,
                'color' => $tier->color,
            ];
        }

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
            (bool) ($model->google2fa_enabled ?? false),
            $model->api_key,
            $model->status ?? 'active',
            $model->uid,
            $model->telegram_chat_id,
            (bool) ($model->telegram_notifications_enabled ?? true),
            (float) ($model->total_deposited ?? 0),
            $tierId,
            $tierArray
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
        $model->total_deposited = $entity->getTotalDeposited();
        $model->membership_tier_id = $entity->getMembershipTierId();
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
