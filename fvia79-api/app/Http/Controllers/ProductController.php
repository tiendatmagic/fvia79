<?php

namespace App\Http\Controllers;

use App\Application\Services\ProductService;
use App\Domain\Entities\Product;
use App\Http\Requests\CreateProductRequest;
use App\Infrastructure\Persistence\Eloquent\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        try {
            $page = $request->query('page');
            $limit = min(100, max(1, (int) $request->query('limit', 8)));
            $categoryId = $request->query('category_id');
            $search = $request->query('search');
            $status = $request->query('status');
            $filterDeleted = $request->query('filter_deleted', 'active');
            $includeOutOfStock = $request->boolean('include_out_of_stock', false);

            $user = $this->getAuthenticatedUser($request);

            $isAdmin = $user && $user->role === 'admin';
            if (! $isAdmin) {
                $filterDeleted = 'active';
                if (! $status) {
                    $status = 'active';
                }
            }

            $lang = $this->resolveLanguage($request);

            if ($page !== null) {
                $pageInt = max(1, (int) $page);
                $result = $this->productService->getActiveProductsPaginated($pageInt, $limit, $categoryId, $status, $search, $filterDeleted, $includeOutOfStock);
                $products = $result['items'];
                $total = $result['total'];

                $data = array_map(fn (Product $product) => $this->serializeProduct($product, true, $lang), $products);

                return response()->json([
                    'data' => $data,
                    'meta' => [
                        'total' => $total,
                        'page' => $pageInt,
                        'limit' => $limit,
                        'last_page' => (int) ceil($total / $limit),
                    ],
                ]);
            }

            $products = $this->productService->getAllActiveProducts($categoryId, $status, $search, $filterDeleted);

            $data = array_map(fn (Product $product) => $this->serializeProduct($product, true, $lang), $products);

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch products.');
        }
    }

    public function indexGrouped(Request $request)
    {
        try {
            $limit = min(100, max(1, (int) $request->query('limit', 8)));
            $search = $request->query('search');
            $includeOutOfStock = $request->boolean('include_out_of_stock', false);
            $lang = $this->resolveLanguage($request);

            $groupedData = $this->productService->getGroupedActiveProducts($limit, $search, $includeOutOfStock);

            $result = array_map(function ($group) use ($lang) {
                $mappedProducts = array_map(fn (Product $product) => $this->serializeProduct($product, false, $lang), $group['products']);

                $catModel = CategoryModel::find($group['id']);
                $groupName = $catModel ? $catModel->getLocalizedName($lang) : $group['name'];
                $groupDesc = $catModel ? $catModel->getLocalizedDescription($lang) : $group['description'];

                return [
                    'id' => $group['id'],
                    'name' => $groupName,
                    'name_vi' => $catModel?->name_vi,
                    'name_en' => $catModel?->name_en,
                    'description' => $groupDesc,
                    'description_vi' => $catModel?->description_vi,
                    'description_en' => $catModel?->description_en,
                    'products' => $mappedProducts,
                    'total' => $group['total'],
                ];
            }, $groupedData);

            return response()->json($result);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch grouped products.');
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $product = $this->productService->getProductById($id);
            if (! $product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            $user = $this->getAuthenticatedUser($request);
            $isAdmin = $user && $user->role === 'admin';
            if ($product->isDeleted() && ! $isAdmin) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            $lang = $this->resolveLanguage($request);

            return response()->json($this->serializeProduct($product, true, $lang));
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch product.');
        }
    }

    public function store(CreateProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct(
                $request->name,
                $request->slug,
                $request->description,
                (float) $request->price,
                $request->status,
                $request->input('attributes'),
                $request->category_id,
                $request->name_vi,
                $request->name_en,
                $request->description_vi,
                $request->description_en,
            );

            return response()->json([
                'message' => 'Product created successfully.',
                'product' => $this->serializeProduct($product, false, $this->resolveLanguage($request)),
            ], 201);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to create product.');
        }
    }

    public function update(CreateProductRequest $request, $id)
    {
        try {
            $product = $this->productService->getProductById($id);
            if (! $product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }
            if ($product->isDeleted()) {
                return response()->json(['message' => 'Cannot update a soft-deleted product.'], 400);
            }

            $product = $this->productService->updateProduct(
                $id,
                $request->name,
                $request->slug,
                $request->description,
                (float) $request->price,
                $request->status,
                $request->input('attributes'),
                $request->category_id,
                $request->name_vi,
                $request->name_en,
                $request->description_vi,
                $request->description_en,
            );

            return response()->json([
                'message' => 'Product updated successfully.',
                'product' => $this->serializeProduct($product, false, $this->resolveLanguage($request)),
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to update product.');
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->productService->deleteProduct($id);
            if (! $deleted) {
                return response()->json(['message' => 'Product not found or delete failed.'], 404);
            }

            return response()->json(['message' => 'Product deleted successfully.']);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to delete product.');
        }
    }

    public function restore($id)
    {
        try {
            $restored = $this->productService->restoreProduct($id);
            if (! $restored) {
                return response()->json(['message' => 'Product not found or restore failed.'], 404);
            }

            return response()->json(['message' => 'Product restored successfully.']);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to restore product.');
        }
    }

    private function getAuthenticatedUser(Request $request)
    {
        $token = $request->cookie('access_token');
        if (! $token) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        if (! $token) {
            return null;
        }

        try {
            $jwtService = app(JwtService::class);
            $payload = $jwtService->validateToken($token);
            if ($payload && ! empty($payload['sub'])) {
                return UserModel::find($payload['sub']);
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    private function resolveLanguage(Request $request): string
    {
        $lang = strtolower(trim((string) ($request->query('lang') ?? $request->header('Accept-Language') ?? 'en')));
        $lang = preg_split('/[-_,;]/', $lang, 2)[0] ?? 'en';

        return in_array($lang, ['en', 'vi'], true) ? $lang : 'en';
    }

    private function serializeProduct(Product $product, bool $includeDeleted = true, string $lang = 'en'): array
    {
        $data = [
            'id' => $product->getId(),
            'category_id' => $product->getCategoryId(),
            'name' => $product->getLocalizedName($lang),
            'name_vi' => $product->getNameVi(),
            'name_en' => $product->getNameEn(),
            'slug' => $product->getSlug(),
            'description' => $product->getLocalizedDescription($lang),
            'description_vi' => $product->getDescriptionVi(),
            'description_en' => $product->getDescriptionEn(),
            'price' => $product->getPrice(),
            'status' => $product->getStatus(),
            'available_count' => $product->getAvailableCount(),
            'attributes' => $product->getAttributes(),
            'supplier' => $product->getSupplierId() ? [
                'code' => $product->getSupplierCode(),
                'name' => $product->getSupplierName(),
                'external_id' => $product->getExternalId(),
                'price_vnd' => $product->getSupplierPriceVnd(),
                'synced_at' => $product->getSupplierSyncedAt()?->format(DATE_ATOM),
            ] : null,
        ];

        if ($includeDeleted) {
            $data['deleted_at'] = $product->getDeletedAt()?->format('Y-m-d H:i:s');
            $data['is_deleted'] = $product->isDeleted();
        }

        return $data;
    }
}
