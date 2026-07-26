<?php

namespace App\Http\Controllers;

use App\Application\Services\MembershipTierService;
use App\Infrastructure\Persistence\Eloquent\MembershipTierModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminMembershipTierController extends Controller
{
    public function __construct(
        private readonly MembershipTierService $membershipTierService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $tiers = $this->membershipTierService->getAllTiersForAdmin();

            return response()->json([
                'data' => $tiers,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch membership tiers.');
        }
    }

    public function publicIndex(): JsonResponse
    {
        $enabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);
        if (! $enabled) {
            return response()->json([
                'data' => [],
            ]);
        }

        try {
            $tiers = $this->membershipTierService->getPublicTiers();

            return response()->json([
                'data' => $tiers,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch public membership tiers.');
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'level' => ['required', 'integer', 'min:1', Rule::unique('membership_tiers', 'level')],
            'name_vi' => ['required', 'string', 'max:100', Rule::unique('membership_tiers', 'name_vi')],
            'name_en' => ['nullable', 'string', 'max:100', Rule::unique('membership_tiers', 'name_en')],
            'min_deposit' => ['required', 'numeric', 'min:0', Rule::unique('membership_tiers', 'min_deposit')],
            'discount_percent' => 'required|numeric|min:0|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ], [
            'level.unique' => 'The tier level has already been taken.',
            'min_deposit.unique' => 'The minimum deposit amount has already been taken by another tier.',
            'name_vi.unique' => 'The Vietnamese tier name has already been taken.',
            'name_en.unique' => 'The English tier name has already been taken.',
        ]);

        $level = (int) $request->input('level');
        $minDeposit = (float) $request->input('min_deposit');

        // Progressive Validation: Higher levels must have a larger min_deposit requirement
        $prevTier = MembershipTierModel::where('level', '<', $level)
            ->orderBy('level', 'desc')
            ->first();
        if ($prevTier && $minDeposit <= (float) $prevTier->min_deposit) {
            throw ValidationException::withMessages([
                'min_deposit' => [
                    sprintf('Minimum deposit for Level %d ($%.2f) must be greater than Level %d ($%.2f).', $level, $minDeposit, $prevTier->level, (float) $prevTier->min_deposit),
                ],
            ]);
        }

        $nextTier = MembershipTierModel::where('level', '>', $level)
            ->orderBy('level', 'asc')
            ->first();
        if ($nextTier && $minDeposit >= (float) $nextTier->min_deposit) {
            throw ValidationException::withMessages([
                'min_deposit' => [
                    sprintf('Minimum deposit for Level %d ($%.2f) must be less than Level %d ($%.2f).', $level, $minDeposit, $nextTier->level, (float) $nextTier->min_deposit),
                ],
            ]);
        }

        try {
            $tier = $this->membershipTierService->createTier($request->all());

            return response()->json([
                'message' => 'Membership tier created successfully.',
                'data' => [
                    'id' => $tier->id,
                    'level' => $tier->level,
                    'name_vi' => $tier->name_vi,
                    'name_en' => $tier->name_en,
                    'min_deposit' => (float) $tier->min_deposit,
                    'discount_percent' => (float) $tier->discount_percent,
                    'icon' => $tier->icon,
                    'color' => $tier->color,
                    'is_active' => (bool) $tier->is_active,
                ],
            ], 201);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to create membership tier.');
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'level' => ['nullable', 'integer', 'min:1', Rule::unique('membership_tiers', 'level')->ignore($id)],
            'name_vi' => ['nullable', 'string', 'max:100', Rule::unique('membership_tiers', 'name_vi')->ignore($id)],
            'name_en' => ['nullable', 'string', 'max:100', Rule::unique('membership_tiers', 'name_en')->ignore($id)],
            'min_deposit' => ['nullable', 'numeric', 'min:0', Rule::unique('membership_tiers', 'min_deposit')->ignore($id)],
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ], [
            'level.unique' => 'The tier level has already been taken.',
            'min_deposit.unique' => 'The minimum deposit amount has already been taken by another tier.',
            'name_vi.unique' => 'The Vietnamese tier name has already been taken.',
            'name_en.unique' => 'The English tier name has already been taken.',
        ]);

        $existingTier = MembershipTierModel::findOrFail($id);
        $level = $request->has('level') ? (int) $request->input('level') : $existingTier->level;
        $minDeposit = $request->has('min_deposit') ? (float) $request->input('min_deposit') : (float) $existingTier->min_deposit;

        // Progressive Validation: Higher levels must have a larger min_deposit requirement
        $prevTier = MembershipTierModel::where('id', '!=', $id)
            ->where('level', '<', $level)
            ->orderBy('level', 'desc')
            ->first();
        if ($prevTier && $minDeposit <= (float) $prevTier->min_deposit) {
            throw ValidationException::withMessages([
                'min_deposit' => [
                    sprintf('Minimum deposit for Level %d ($%.2f) must be greater than Level %d ($%.2f).', $level, $minDeposit, $prevTier->level, (float) $prevTier->min_deposit),
                ],
            ]);
        }

        $nextTier = MembershipTierModel::where('id', '!=', $id)
            ->where('level', '>', $level)
            ->orderBy('level', 'asc')
            ->first();
        if ($nextTier && $minDeposit >= (float) $nextTier->min_deposit) {
            throw ValidationException::withMessages([
                'min_deposit' => [
                    sprintf('Minimum deposit for Level %d ($%.2f) must be less than Level %d ($%.2f).', $level, $minDeposit, $nextTier->level, (float) $nextTier->min_deposit),
                ],
            ]);
        }

        try {
            $tier = $this->membershipTierService->updateTier($id, $request->all());

            return response()->json([
                'message' => 'Membership tier updated successfully.',
                'data' => [
                    'id' => $tier->id,
                    'level' => $tier->level,
                    'name_vi' => $tier->name_vi,
                    'name_en' => $tier->name_en,
                    'min_deposit' => (float) $tier->min_deposit,
                    'discount_percent' => (float) $tier->discount_percent,
                    'icon' => $tier->icon,
                    'color' => $tier->color,
                    'is_active' => (bool) $tier->is_active,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to update membership tier.');
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->membershipTierService->deleteTier($id);

            return response()->json([
                'message' => 'Membership tier deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to delete membership tier.');
        }
    }
}
