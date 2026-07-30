<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Eloquent\MembershipTierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Support\Str;

class MembershipTierService
{

    public function recalculateUserTier(UserModel $user): ?MembershipTierModel
    {
        $enabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);
        if (! $enabled) {
            if ($user->membership_tier_id !== null) {
                $user->membership_tier_id = null;
                $user->save();
            }

            return null;
        }

        $totalDeposited = (float) ($user->total_deposited ?? 0);

        $matchingTier = MembershipTierModel::where('is_active', true)
            ->where('min_deposit', '<=', $totalDeposited)
            ->orderBy('min_deposit', 'desc')
            ->orderBy('level', 'desc')
            ->first();

        $newTierId = $matchingTier?->id;

        if ($user->membership_tier_id !== $newTierId) {
            $user->membership_tier_id = $newTierId;
            $user->save();
        }

        return $matchingTier;
    }

    public function getPublicTiers(): array
    {
        return MembershipTierModel::where('is_active', true)
            ->orderBy('min_deposit', 'asc')
            ->orderBy('level', 'asc')
            ->get()
            ->map(fn ($tier) => [
                'id' => $tier->id,
                'level' => $tier->level,
                'name_vi' => $tier->name_vi,
                'name_en' => $tier->name_en,
                'min_deposit' => (float) $tier->min_deposit,
                'discount_percent' => (float) $tier->discount_percent,
                'icon' => $tier->icon,
                'color' => $tier->color,
            ])
            ->toArray();
    }

    public function getAllTiersForAdmin(): array
    {
        return MembershipTierModel::orderBy('min_deposit', 'asc')
            ->orderBy('level', 'asc')
            ->get()
            ->map(fn ($tier) => [
                'id' => $tier->id,
                'level' => $tier->level,
                'name_vi' => $tier->name_vi,
                'name_en' => $tier->name_en,
                'min_deposit' => (float) $tier->min_deposit,
                'discount_percent' => (float) $tier->discount_percent,
                'icon' => $tier->icon,
                'color' => $tier->color,
                'is_active' => (bool) $tier->is_active,
                'users_count' => $tier->users()->count(),
                'created_at' => $tier->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $tier->updated_at?->format('Y-m-d H:i:s'),
            ])
            ->toArray();
    }

    public function createTier(array $data): MembershipTierModel
    {
        $tier = new MembershipTierModel();
        $tier->id = (string) Str::uuid7();
        $tier->level = (int) ($data['level'] ?? 1);
        $tier->name_vi = trim($data['name_vi']);
        $tier->name_en = trim($data['name_en'] ?? $data['name_vi']);
        $tier->min_deposit = (float) $data['min_deposit'];
        $tier->discount_percent = (float) $data['discount_percent'];
        $tier->icon = $data['icon'] ?? 'shield';
        $tier->color = $data['color'] ?? '#94a3b8';
        $tier->is_active = (bool) ($data['is_active'] ?? true);
        $tier->save();

        $this->syncAllUserTiers();

        return $tier;
    }

    public function updateTier(string $id, array $data): MembershipTierModel
    {
        $tier = MembershipTierModel::findOrFail($id);
        if (isset($data['level'])) {
            $tier->level = (int) $data['level'];
        }
        if (isset($data['name_vi'])) {
            $tier->name_vi = trim($data['name_vi']);
        }
        if (isset($data['name_en'])) {
            $tier->name_en = trim($data['name_en']);
        }
        if (isset($data['min_deposit'])) {
            $tier->min_deposit = (float) $data['min_deposit'];
        }
        if (isset($data['discount_percent'])) {
            $tier->discount_percent = (float) $data['discount_percent'];
        }
        if (isset($data['icon'])) {
            $tier->icon = $data['icon'];
        }
        if (isset($data['color'])) {
            $tier->color = $data['color'];
        }
        if (isset($data['is_active'])) {
            $tier->is_active = (bool) $data['is_active'];
        }
        $tier->save();

        $this->syncAllUserTiers();

        return $tier;
    }

    public function deleteTier(string $id): bool
    {
        $tier = MembershipTierModel::findOrFail($id);
        $deleted = $tier->delete();

        if ($deleted) {
            $this->syncAllUserTiers();
        }

        return $deleted;
    }

    public function syncAllUserTiers(): void
    {
        UserModel::chunk(100, function ($users) {
            foreach ($users as $user) {
                $this->recalculateUserTier($user);
            }
        });
    }
}
