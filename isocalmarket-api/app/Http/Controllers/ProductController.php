<?php

namespace App\Http\Controllers;

use App\Application\Services\ProductService;
use App\Domain\Entities\Product;
use App\Http\Requests\CreateProductRequest;
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
            $limit = $request->query('limit', 8);
            $categoryId = $request->query('category_id');
            $search = $request->query('search');
            $status = $request->query('status');
            $filterDeleted = $request->query('filter_deleted', 'active');

            $user = $this->getAuthenticatedUser($request);

            $isAdmin = $user && $user->role === 'admin';
            if (! $isAdmin) {
                $filterDeleted = 'active';
                if (! $status) {
                    $status = 'active';
                }
            }

            if ($page) {
                $result = $this->productService->getActiveProductsPaginated((int) $page, (int) $limit, $categoryId, $status, $search, $filterDeleted);
                $products = $result['items'];
                $total = $result['total'];

                $data = array_map(fn (Product $product) => $this->serializeProduct($product), $products);

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

            $products = $this->productService->getAllActiveProducts($categoryId, $status, $search, $filterDeleted);

            $data = array_map(fn (Product $product) => $this->serializeProduct($product), $products);

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch products.');
        }
    }

    public function indexGrouped(Request $request)
    {
        try {
            $limit = (int) $request->query('limit', 8);
            $search = $request->query('search');

            $groupedData = $this->productService->getGroupedActiveProducts($limit, $search);

            $result = array_map(function ($group) {
                $mappedProducts = array_map(fn (Product $product) => $this->serializeProduct($product, false), $group['products']);

                return [
                    'id' => $group['id'],
                    'name' => $group['name'],
                    'description' => $group['description'],
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

            return response()->json($this->serializeProduct($product));
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
                $request->category_id
            );

            return response()->json([
                'message' => 'Product created successfully.',
                'product' => [
                    'id' => $product->getId(),
                    'category_id' => $product->getCategoryId(),
                    'name' => $product->getName(),
                    'slug' => $product->getSlug(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'status' => $product->getStatus(),
                    'attributes' => $product->getAttributes(),
                ],
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
                $request->category_id
            );

            return response()->json([
                'message' => 'Product updated successfully.',
                'product' => [
                    'id' => $product->getId(),
                    'category_id' => $product->getCategoryId(),
                    'name' => $product->getName(),
                    'slug' => $product->getSlug(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'status' => $product->getStatus(),
                    'attributes' => $product->getAttributes(),
                ],
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

    private function serializeProduct(Product $product, bool $includeDeleted = true): array
    {
        $data = [
            'id' => $product->getId(),
            'category_id' => $product->getCategoryId(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'description' => $product->getDescription(),
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
