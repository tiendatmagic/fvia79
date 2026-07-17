<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Application\Services\ProductService;
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
            $limit = $request->query('limit', 10);
            $categoryId = $request->query('category_id');
            $search = $request->query('search');
            $status = $request->query('status');

            // Default behavior for storefront / guest is to show active status only
            $user = null;
            try {
                $user = auth('api')->user();
            } catch (\Exception $e) {}

            $isAdmin = $user && $user->role === 'admin';
            if (!$isAdmin && !$status) {
                $status = 'active';
            }

            if ($page) {
                $result = $this->productService->getActiveProductsPaginated((int)$page, (int)$limit, $categoryId, $status, $search);
                $products = $result['items'];
                $total = $result['total'];

                $data = array_map(function ($product) {
                    return [
                        'id' => $product->getId(),
                        'category_id' => $product->getCategoryId(),
                        'name' => $product->getName(),
                        'slug' => $product->getSlug(),
                        'description' => $product->getDescription(),
                        'price' => $product->getPrice(),
                        'status' => $product->getStatus(),
                        'available_count' => $product->getAvailableCount(),
                        'attributes' => $product->getAttributes()
                    ];
                }, $products);

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

            $products = $this->productService->getAllActiveProducts($categoryId, $status, $search);
            
            $data = array_map(function ($product) {
                return [
                    'id' => $product->getId(),
                    'category_id' => $product->getCategoryId(),
                    'name' => $product->getName(),
                    'slug' => $product->getSlug(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'status' => $product->getStatus(),
                    'available_count' => $product->getAvailableCount(),
                    'attributes' => $product->getAttributes()
                ];
            }, $products);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        try {
            $product = $this->productService->getProductById($id);
            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            return response()->json([
                'id' => $product->getId(),
                'category_id' => $product->getCategoryId(),
                'name' => $product->getName(),
                'slug' => $product->getSlug(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'status' => $product->getStatus(),
                'available_count' => $product->getAvailableCount(),
                'attributes' => $product->getAttributes()
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(CreateProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct(
                $request->name,
                $request->slug,
                $request->description,
                (float)$request->price,
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
                    'attributes' => $product->getAttributes()
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(CreateProductRequest $request, $id)
    {
        try {
            $product = $this->productService->updateProduct(
                $id,
                $request->name,
                $request->slug,
                $request->description,
                (float)$request->price,
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
                    'attributes' => $product->getAttributes()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->productService->deleteProduct($id);
            if (!$deleted) {
                return response()->json(['message' => 'Product not found or delete failed.'], 404);
            }

            return response()->json(['message' => 'Product deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
