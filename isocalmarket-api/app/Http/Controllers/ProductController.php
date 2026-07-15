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
            $limit = $request->query('limit', 12); // Mặc định 12 sản phẩm/trang

            if ($page) {
                $result = $this->productService->getActiveProductsPaginated((int)$page, (int)$limit);
                $products = $result['items'];
                $total = $result['total'];

                $data = array_map(function ($product) {
                    return [
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'slug' => $product->getSlug(),
                        'description' => $product->getDescription(),
                        'price' => $product->getPrice(),
                        'status' => $product->getStatus(),
                        'available_count' => $product->getAvailableCount()
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

            $products = $this->productService->getAllActiveProducts();
            
            $data = array_map(function ($product) {
                return [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'slug' => $product->getSlug(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'status' => $product->getStatus(),
                    'available_count' => $product->getAvailableCount()
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
                'name' => $product->getName(),
                'slug' => $product->getSlug(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'status' => $product->getStatus(),
                'available_count' => $product->getAvailableCount()
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
                $request->status
            );

            return response()->json([
                'message' => 'Product created successfully.',
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'slug' => $product->getSlug(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'status' => $product->getStatus()
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
                $request->status
            );

            return response()->json([
                'message' => 'Product updated successfully.',
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'slug' => $product->getSlug(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'status' => $product->getStatus()
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
