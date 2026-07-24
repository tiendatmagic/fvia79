<?php

namespace App\Http\Controllers;

use App\Models\FaqModel;
use App\Models\WarrantyPolicyModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Public API: Get active warranty policies and FAQs
     */
    public function indexPublic(): JsonResponse
    {
        $policies = WarrantyPolicyModel::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $faqs = FaqModel::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'policies' => $policies,
            'faqs' => $faqs,
        ]);
    }

    /**
     * Admin API: Get all warranty policies and FAQs (including inactive)
     */
    public function indexAdmin(): JsonResponse
    {
        $policies = WarrantyPolicyModel::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $faqs = FaqModel::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'policies' => $policies,
            'faqs' => $faqs,
        ]);
    }

    /**
     * Admin API: Store warranty policy
     */
    public function storePolicy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:bm,via',
            'type' => 'required|string|in:accepted,rejected',
            'content_vi' => 'required|string',
            'content_en' => 'required|string',
            'sub_content_vi' => 'nullable|string',
            'sub_content_en' => 'nullable|string',
            'tag_vi' => 'nullable|string',
            'tag_en' => 'nullable|string',
            'tag_color' => 'nullable|string|in:blue,emerald,amber,rose',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $policy = WarrantyPolicyModel::create([
            'category' => $validated['category'],
            'type' => $validated['type'],
            'content_vi' => $validated['content_vi'],
            'content_en' => $validated['content_en'],
            'sub_content_vi' => $validated['sub_content_vi'] ?? null,
            'sub_content_en' => $validated['sub_content_en'] ?? null,
            'tag_vi' => $validated['tag_vi'] ?? null,
            'tag_en' => $validated['tag_en'] ?? null,
            'tag_color' => $validated['tag_color'] ?? 'blue',
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Warranty policy created successfully',
            'data' => $policy,
        ], 201);
    }

    /**
     * Admin API: Update warranty policy
     */
    public function updatePolicy(Request $request, int $id): JsonResponse
    {
        $policy = WarrantyPolicyModel::find($id);

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Warranty policy not found',
            ], 404);
        }

        $validated = $request->validate([
            'category' => 'sometimes|string|in:bm,via',
            'type' => 'sometimes|string|in:accepted,rejected',
            'content_vi' => 'sometimes|string',
            'content_en' => 'sometimes|string',
            'sub_content_vi' => 'nullable|string',
            'sub_content_en' => 'nullable|string',
            'tag_vi' => 'nullable|string',
            'tag_en' => 'nullable|string',
            'tag_color' => 'nullable|string|in:blue,emerald,amber,rose',
            'sort_order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $policy->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Warranty policy updated successfully',
            'data' => $policy,
        ]);
    }

    /**
     * Admin API: Delete warranty policy
     */
    public function destroyPolicy(int $id): JsonResponse
    {
        $policy = WarrantyPolicyModel::find($id);

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Warranty policy not found',
            ], 404);
        }

        $policy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warranty policy deleted successfully',
        ]);
    }

    /**
     * Admin API: Store FAQ
     */
    public function storeFaq(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_vi' => 'required|string',
            'question_en' => 'required|string',
            'answer_vi' => 'required|string',
            'answer_en' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $faq = FaqModel::create([
            'question_vi' => $validated['question_vi'],
            'question_en' => $validated['question_en'],
            'answer_vi' => $validated['answer_vi'],
            'answer_en' => $validated['answer_en'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FAQ created successfully',
            'data' => $faq,
        ], 201);
    }

    /**
     * Admin API: Update FAQ
     */
    public function updateFaq(Request $request, int $id): JsonResponse
    {
        $faq = FaqModel::find($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found',
            ], 404);
        }

        $validated = $request->validate([
            'question_vi' => 'sometimes|string',
            'question_en' => 'sometimes|string',
            'answer_vi' => 'sometimes|string',
            'answer_en' => 'sometimes|string',
            'sort_order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $faq->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully',
            'data' => $faq,
        ]);
    }

    /**
     * Admin API: Delete FAQ
     */
    public function destroyFaq(int $id): JsonResponse
    {
        $faq = FaqModel::find($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found',
            ], 404);
        }

        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully',
        ]);
    }
}
