<?php

use App\Infrastructure\Persistence\Eloquent\MembershipTierModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $enabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);
        if (! $enabled || ! Schema::hasTable('membership_tiers') || ! Schema::hasTable('users')) {
            return;
        }

        $tiers = MembershipTierModel::where('is_active', true)
            ->orderByDesc('min_deposit')
            ->orderByDesc('level')
            ->get(['id', 'min_deposit']);

        if ($tiers->isEmpty()) {
            return;
        }

        UserModel::whereNull('membership_tier_id')->chunk(100, function ($users) use ($tiers) {
            foreach ($users as $user) {
                $totalDeposited = (float) ($user->total_deposited ?? 0);
                $matchingTier = $tiers->first(
                    fn ($tier) => (float) $tier->min_deposit <= $totalDeposited
                );

                if ($matchingTier) {
                    $user->membership_tier_id = $matchingTier->id;
                    $user->save();
                }
            }
        });
    }

    public function down(): void
    {
        // Safe migration: no destructive action on rollback
    }
};
