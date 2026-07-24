<?php

namespace App\Http\Controllers;

use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Http\Requests\CreateCategoryRequest;
use App\Application\Services\CategoryService;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    private CategoryRepositoryInterface $categoryRepo;
    private CategoryService $categoryService;

    public function __construct(
        CategoryRepositoryInterface $categoryRepo,
        CategoryService $categoryService
    ) {
        $this->categoryRepo = $categoryRepo;
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        try {
            $page = $request->query('page');
            $limit = $request->query('limit', 10);
            $search = $request->query('search');
            $lang = strtolower(trim($request->query('lang') ?? $request->header('Accept-Language') ?? 'en'));
            if (! in_array($lang, ['en', 'vi'], true)) {
                $lang = 'en';
            }

            if ($page) {
                $result = $this->categoryService->getCategoriesPaginated((int)$page, (int)$limit, $search);
                $categories = $result['items'];
                $total = $result['total'];

                $data = array_map(function ($cat) use ($lang) {
                    $catModel = CategoryModel::find($cat->getId());
                    return [
                        'id' => $cat->getId(),
                        'name' => $catModel ? $catModel->getLocalizedName($lang) : $cat->getName(),
                        'name_vi' => $catModel?->name_vi,
                        'name_en' => $catModel?->name_en,
                        'slug' => $cat->getSlug(),
                        'description' => $catModel ? $catModel->getLocalizedDescription($lang) : $cat->getDescription(),
                        'description_vi' => $catModel?->description_vi,
                        'description_en' => $catModel?->description_en,
                        'is_active' => $cat->getIsActive(),
                        'products_count' => $cat->getProductsCount() ?? 0,
                        'created_at' => $cat->getCreatedAt() ? $cat->getCreatedAt()->format('Y-m-d H:i:s') : null
                    ];
                }, $categories);

                return response()->json([
                    'data' => $data,
                    'meta' => [
                        'total' => $total,
                        'page' => (int)$page,
                        'limit' => (int)$limit,
                        'last_page' => (int)ceil($total / $limit)
                    ]
                ]);
            }

            $cacheKey = "categories_active_storefront_{$lang}";
            if (empty($search)) {
                $data = Cache::remember($cacheKey, 600, function () use ($search, $lang) {
                    $categories = $this->categoryRepo->findAll($search);
                    return array_map(function ($cat) use ($lang) {
                        $catModel = CategoryModel::find($cat->getId());
                        return [
                            'id' => $cat->getId(),
                            'name' => $catModel ? $catModel->getLocalizedName($lang) : $cat->getName(),
                            'name_vi' => $catModel?->name_vi,
                            'name_en' => $catModel?->name_en,
                            'slug' => $cat->getSlug(),
                            'description' => $catModel ? $catModel->getLocalizedDescription($lang) : $cat->getDescription(),
                            'description_vi' => $catModel?->description_vi,
                            'description_en' => $catModel?->description_en,
                            'is_active' => $cat->getIsActive(),
                            'products_count' => $cat->getProductsCount() ?? 0,
                            'created_at' => $cat->getCreatedAt() ? $cat->getCreatedAt()->format('Y-m-d H:i:s') : null
                        ];
                    }, $categories);
                });
                return response()->json($data);
            }

            $categories = $this->categoryRepo->findAll($search);
            $data = array_map(function ($cat) use ($lang) {
                $catModel = CategoryModel::find($cat->getId());
                return [
                    'id' => $cat->getId(),
                    'name' => $catModel ? $catModel->getLocalizedName($lang) : $cat->getName(),
                    'name_vi' => $catModel?->name_vi,
                    'name_en' => $catModel?->name_en,
                    'slug' => $cat->getSlug(),
                    'description' => $catModel ? $catModel->getLocalizedDescription($lang) : $cat->getDescription(),
                    'description_vi' => $catModel?->description_vi,
                    'description_en' => $catModel?->description_en,
                    'is_active' => $cat->getIsActive(),
                    'products_count' => $cat->getProductsCount() ?? 0,
                    'created_at' => $cat->getCreatedAt() ? $cat->getCreatedAt()->format('Y-m-d H:i:s') : null
                ];
            }, $categories);

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch categories.');
        }
    }

    public function store(CreateCategoryRequest $request)
    {
        try {
            $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;
            $category = $this->categoryService->createCategory(
                $request->name,
                $request->slug,
                $request->description,
                $isActive
            );

            $nameVi = $request->name_vi ?: ($request->name_en ?: $request->name);
            $nameEn = $request->name_en ?: ($request->name_vi ?: $request->name);
            $descVi = $request->description_vi ?? ($request->description_en ?? $request->description);
            $descEn = $request->description_en ?? ($request->description_vi ?? $request->description);

            $model = CategoryModel::find($category->getId());
            if ($model) {
                $model->update([
                    'name_vi' => $nameVi,
                    'name_en' => $nameEn,
                    'description_vi' => $descVi,
                    'description_en' => $descEn,
                ]);
            }

            Cache::forget('categories_active_storefront_en');
            Cache::forget('categories_active_storefront_vi');
            return response()->json([
                'message' => 'Category created successfully.',
                'category' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'name_vi' => $nameVi,
                    'name_en' => $nameEn,
                    'slug' => $category->getSlug(),
                    'description' => $category->getDescription(),
                    'description_vi' => $descVi,
                    'description_en' => $descEn,
                    'is_active' => $category->getIsActive(),
                    'created_at' => $category->getCreatedAt() ? $category->getCreatedAt()->format('Y-m-d H:i:s') : null
                ]
            ], 201);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to create category.');
        }
    }

    public function update(CreateCategoryRequest $request, $id)
    {
        try {
            $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;
            $category = $this->categoryService->updateCategory(
                $id,
                $request->name,
                $request->slug,
                $request->description,
                $isActive
            );

            $nameVi = $request->name_vi ?: ($request->name_en ?: $request->name);
            $nameEn = $request->name_en ?: ($request->name_vi ?: $request->name);
            $descVi = $request->description_vi ?? ($request->description_en ?? $request->description);
            $descEn = $request->description_en ?? ($request->description_vi ?? $request->description);

            $model = CategoryModel::find($category->getId());
            if ($model) {
                $model->update([
                    'name_vi' => $nameVi,
                    'name_en' => $nameEn,
                    'description_vi' => $descVi,
                    'description_en' => $descEn,
                ]);
            }

            Cache::forget('categories_active_storefront_en');
            Cache::forget('categories_active_storefront_vi');
            return response()->json([
                'message' => 'Category updated successfully.',
                'category' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'name_vi' => $nameVi,
                    'name_en' => $nameEn,
                    'slug' => $category->getSlug(),
                    'description' => $category->getDescription(),
                    'description_vi' => $descVi,
                    'description_en' => $descEn,
                    'is_active' => $category->getIsActive(),
                    'created_at' => $category->getCreatedAt() ? $category->getCreatedAt()->format('Y-m-d H:i:s') : null
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to update category.');
        }
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->deleteCategory($id);
            Cache::forget('categories_active_storefront');
            return response()->json(['message' => 'Category deleted successfully.']);
        } catch (\Exception $e) {
            if (in_array($e->getMessage(), [
                'Category not found.',
                'Cannot delete category containing products.',
            ], true)) {
                return response()->json(['message' => $e->getMessage()], 400);
            }

            return $this->serverError($e, 'Failed to delete category.');
        }
    }

    public function deactivateOutOfStockCategories()
    {
        try {
            $categories = CategoryModel::where('is_active', true)->get();
            $deactivatedCount = 0;

            foreach ($categories as $category) {
                $products = ProductModel::where('category_id', $category->id)->get();

                $totalStock = 0;
                foreach ($products as $product) {
                    if ($product->status !== 'active') {
                        continue;
                    }
                    $supplierStock = max(0, (int) ($product->supplier_stock ?? 0));
                    $localStock = (int) $product->inventoryItems()->where('status', 'available')->count();
                    $totalStock += ($supplierStock + $localStock);
                }

                if ($totalStock <= 0) {
                    $category->is_active = false;
                    $category->save();
                    $deactivatedCount++;
                }
            }

            if ($deactivatedCount > 0) {
                Cache::forget('categories_active_storefront');
                Cache::forget('products_active_storefront');
            }

            return response()->json([
                'message' => "Deactivated {$deactivatedCount} out-of-stock categories.",
                'count' => $deactivatedCount,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to deactivate out of stock categories.');
        }
    }
}
