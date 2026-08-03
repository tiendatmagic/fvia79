<?php

namespace App\Http\Controllers;

use App\Application\Services\CategoryService;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Http\Requests\CreateCategoryRequest;
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
            $limit = min(20, max(1, (int) $request->query('limit', 10)));
            $search = $request->query('search');
            $lang = $this->resolveLanguage($request);

            if ($page) {
                $result = $this->categoryService->getCategoriesPaginated((int) $page, (int) $limit, $search);
                $categories = $result['items'];
                $total = $result['total'];

                $data = array_map(fn ($cat) => $this->serializeCategory($cat, $lang), $categories);

                return response()->json([
                    'data' => $data,
                    'meta' => [
                        'total' => $total,
                        'page' => (int) $page,
                        'limit' => (int) $limit,
                        'last_page' => (int) ceil($total / $limit),
                    ],
                ]);
            }

            $cacheKey = "categories_active_storefront_{$lang}";
            if ($request->boolean('refresh') || $request->boolean('clear_cache')) {
                Cache::forget($cacheKey);
                Cache::forget('categories_active_storefront');
                Cache::forget('categories_active_storefront_vi');
                Cache::forget('categories_active_storefront_en');
                Cache::forget('products_active_storefront');
            }
            if (empty($search)) {
                $data = Cache::remember($cacheKey, 600, function () use ($search, $lang) {
                    $categories = $this->categoryRepo->findAll($search);

                    return array_map(fn ($cat) => $this->serializeCategory($cat, $lang), $categories);
                });

                return response()->json($data);
            }

            $categories = $this->categoryRepo->findAll($search);
            $data = array_map(fn ($cat) => $this->serializeCategory($cat, $lang), $categories);

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch categories.');
        }
    }

    public function store(CreateCategoryRequest $request)
    {
        try {
            $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;
            $sortOrder = (int) $request->input('sort_order', 0);
            $category = $this->categoryService->createCategory(
                $request->name,
                $request->slug,
                $request->description,
                $isActive,
                $request->name_vi,
                $request->name_en,
                $request->description_vi,
                $request->description_en,
                $sortOrder,
            );

            $this->forgetCategoryCaches();

            return response()->json([
                'message' => 'Category created successfully.',
                'category' => $this->serializeCategory($category, $this->resolveLanguage($request)),
            ], 201);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to create category.');
        }
    }

    public function update(CreateCategoryRequest $request, $id)
    {
        try {
            $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;
            $sortOrder = (int) $request->input('sort_order', 0);
            $category = $this->categoryService->updateCategory(
                $id,
                $request->name,
                $request->slug,
                $request->description,
                $isActive,
                $request->name_vi,
                $request->name_en,
                $request->description_vi,
                $request->description_en,
                $sortOrder,
            );

            $this->forgetCategoryCaches();

            return response()->json([
                'message' => 'Category updated successfully.',
                'category' => $this->serializeCategory($category, $this->resolveLanguage($request)),
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to update category.');
        }
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->deleteCategory($id);
            $this->forgetCategoryCaches();

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
                $this->forgetCategoryCaches();
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

    public function reorder(Request $request)
    {
        try {
            $items = $request->input('items', []);
            if (! is_array($items)) {
                return response()->json(['message' => 'Invalid items format.'], 400);
            }

            $this->categoryService->reorderCategories($items);
            $this->forgetCategoryCaches();

            return response()->json(['message' => 'Category order updated successfully.']);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to reorder categories.');
        }
    }

    private function resolveLanguage(Request $request): string
    {
        $lang = strtolower(trim((string) ($request->query('lang') ?? $request->header('Accept-Language') ?? 'en')));
        $lang = preg_split('/[-_,;]/', $lang, 2)[0] ?? 'en';

        return in_array($lang, ['en', 'vi'], true) ? $lang : 'en';
    }

    private function serializeCategory($category, string $lang): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getLocalizedName($lang),
            'name_vi' => $category->getNameVi(),
            'name_en' => $category->getNameEn(),
            'slug' => $category->getSlug(),
            'description' => $category->getLocalizedDescription($lang),
            'description_vi' => $category->getDescriptionVi(),
            'description_en' => $category->getDescriptionEn(),
            'is_active' => $category->getIsActive(),
            'sort_order' => $category->getSortOrder(),
            'products_count' => $category->getProductsCount() ?? 0,
            'created_at' => $category->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    private function forgetCategoryCaches(): void
    {
        Cache::forget('categories_active_storefront');
        Cache::forget('categories_active_storefront_en');
        Cache::forget('categories_active_storefront_vi');
        Cache::forget('products_active_storefront');
    }
}
