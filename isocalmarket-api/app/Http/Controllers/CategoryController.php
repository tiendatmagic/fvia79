<?php

namespace App\Http\Controllers;

use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Http\Requests\CreateCategoryRequest;
use App\Application\Services\CategoryService;
use Illuminate\Http\Request;

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

    public function index()
    {
        try {
            $categories = $this->categoryRepo->findAll();
            $data = array_map(function ($cat) {
                return [
                    'id' => $cat->getId(),
                    'name' => $cat->getName(),
                    'slug' => $cat->getSlug(),
                    'description' => $cat->getDescription()
                ];
            }, $categories);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Cannot fetch categories: ' . $e->getMessage()], 500);
        }
    }

    public function store(CreateCategoryRequest $request)
    {
        try {
            $category = $this->categoryService->createCategory(
                $request->name,
                $request->slug,
                $request->description
            );

            return response()->json([
                'message' => 'Category created successfully.',
                'category' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                    'description' => $category->getDescription()
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(CreateCategoryRequest $request, $id)
    {
        try {
            $category = $this->categoryService->updateCategory(
                $id,
                $request->name,
                $request->slug,
                $request->description
            );

            return response()->json([
                'message' => 'Category updated successfully.',
                'category' => [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                    'description' => $category->getDescription()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->deleteCategory($id);
            return response()->json(['message' => 'Category deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
