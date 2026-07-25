<?php

namespace App\Http\Controllers;

use App\Application\Services\MembershipTierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $enabled = filter_var(config('app.feature_membership_tiers_enabled', true), FILTER_VALIDATE_BOOLEAN);
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
            'level' => 'required|integer|min:1',
            'name_vi' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'min_deposit' => 'required|numeric|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

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
            'level' => 'nullable|integer|min:1',
            'name_vi' => 'nullable|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'min_deposit' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

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
