<?php

namespace App\Http\Controllers;

use App\Models\FaqModel;
use App\Models\PolicyCategoryModel;
use App\Models\WarrantyPolicyModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqController extends Controller
{

    public function indexPublic(): JsonResponse
    {
        $categories = PolicyCategoryModel::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

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
            'categories' => $categories,
            'policies' => $policies,
            'faqs' => $faqs,
        ]);
    }

    public function indexAdmin(): JsonResponse
    {
        $categories = PolicyCategoryModel::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $policies = WarrantyPolicyModel::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $faqs = FaqModel::orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'policies' => $policies,
            'faqs' => $faqs,
        ]);
    }

    public function storePolicyCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'nullable|string|max:50|unique:policy_categories,key',
            'name_vi' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $key = !empty($validated['key']) ? Str::slug($validated['key'], '_') : Str::slug($validated['name_en'], '_');
        if (empty($key)) {
            $key = 'cat_' . time();
        }

        $originalKey = $key;
        $counter = 1;
        while (PolicyCategoryModel::where('key', $key)->exists()) {
            $key = $originalKey . '_' . $counter++;
        }

        $category = PolicyCategoryModel::create([
            'key' => $key,
            'name_vi' => $validated['name_vi'],
            'name_en' => $validated['name_en'],
            'icon' => $validated['icon'] ?? 'package',
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Policy category created successfully',
            'data' => $category,
        ], 201);
    }

    public function updatePolicyCategory(Request $request, int $id): JsonResponse
    {
        $category = PolicyCategoryModel::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Policy category not found',
            ], 404);
        }

        $validated = $request->validate([
            'key' => 'sometimes|string|max:50|unique:policy_categories,key,' . $id,
            'name_vi' => 'sometimes|string|max:100',
            'name_en' => 'sometimes|string|max:100',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $oldKey = $category->key;

        if (isset($validated['key'])) {
            $validated['key'] = Str::slug($validated['key'], '_');
        }

        $category->update($validated);

        if (isset($validated['key']) && $validated['key'] !== $oldKey) {
            WarrantyPolicyModel::where('category', $oldKey)->update(['category' => $validated['key']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Policy category updated successfully',
            'data' => $category,
        ]);
    }

    public function destroyPolicyCategory(int $id): JsonResponse
    {
        $category = PolicyCategoryModel::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Policy category not found',
            ], 404);
        }

        $inUse = WarrantyPolicyModel::where('category', $category->key)->exists();
        if ($inUse) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete policy category because it is assigned to existing warranty policies',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Policy category deleted successfully',
        ]);
    }

    public function storePolicy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|max:50',
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
            'category' => 'sometimes|string|max:50',
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
