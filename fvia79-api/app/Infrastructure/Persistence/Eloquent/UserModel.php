<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

#[Fillable(['uid', 'username', 'name', 'email', 'email_verified_at', 'phone', 'telegram_chat_id', 'telegram_notifications_enabled', 'password', 'role', 'balance', 'total_deposited', 'membership_tier_id', 'refresh_token_hash', 'refresh_token_expired_at', 'google2fa_secret', 'google2fa_enabled', 'api_key', 'api_key_hash', 'status', 'token_version'])]
#[Hidden(['password', 'remember_token'])]
class UserModel extends Authenticatable
{
    use HasFactory, HasUuids, NormalizeTextTrait, Notifiable;

    protected $table = 'users';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = self::generateUniqueUid();
            }
        });

        static::saving(function ($model) {
            if ($model->isDirty('name')) {
                $model->normalized_name = self::normalizeText($model->name);
            }
            if ($model->isDirty('api_key')) {
                $model->api_key_hash = $model->api_key ? hash('sha256', $model->api_key) : null;
            }

            // Tự động tính toán & gán membership_tier_id chính xác theo total_deposited ngay khi lưu
            if (($model->isDirty('total_deposited') || empty($model->membership_tier_id)) && Schema::hasTable('membership_tiers')) {
                $featureEnabled = filter_var(config('app.feature_membership_tiers_enabled', true), FILTER_VALIDATE_BOOLEAN);
                $dbEnabled = filter_var(app(\App\Application\Services\SystemSettingService::class)->getSetting('membership_tiers_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
                if ($featureEnabled && $dbEnabled) {
                    $totalDeposited = (float) ($model->total_deposited ?? 0);
                    $matchingTier = MembershipTierModel::where('is_active', true)
                        ->where('min_deposit', '<=', $totalDeposited)
                        ->orderByDesc('min_deposit')
                        ->orderByDesc('level')
                        ->first();
                    if ($matchingTier) {
                        $model->membership_tier_id = $matchingTier->id;
                    }
                }
            }
        });
    }

    public static function generateUniqueUid(): string
    {
        static $lastCandidateInt = 0;

        $baseEpoch = 1767225600;
        $diff = max(0, time() - $baseEpoch);
        $candidateInt = 10000000 + $diff;

        if ($candidateInt <= $lastCandidateInt) {
            $candidateInt = $lastCandidateInt + 1;
        }

        $candidate = (string) $candidateInt;

        while (static::where('uid', $candidate)->exists()) {
            $candidateInt++;
            $candidate = (string) $candidateInt;
        }

        $lastCandidateInt = (int) $candidate;

        return $candidate;
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    public static function generateUniqueApiKey(): string
    {
        do {
            $key = Str::random(32);
        } while (static::where('api_key_hash', hash('sha256', $key))->exists());

        return $key;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'refresh_token_expired_at' => 'datetime',
            'google2fa_enabled' => 'boolean',
            'telegram_notifications_enabled' => 'boolean',
            'balance' => 'float',
            'total_deposited' => 'float',
            'token_version' => 'integer',
            'api_key' => 'encrypted',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderModel::class, 'user_id');
    }

    public function membershipTier(): BelongsTo
    {
        return $this->belongsTo(MembershipTierModel::class, 'membership_tier_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSessionModel::class, 'user_id');
    }
}
