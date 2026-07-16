<?php

namespace App\Http\Controllers;

use App\Domain\Repositories\CategoryRepositoryInterface;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private CategoryRepositoryInterface $categoryRepo;

    public function __construct(CategoryRepositoryInterface $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
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
}
