<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    // Arbre complet des catégories racines avec enfants
    public function index(): JsonResponse
    {
        $categories = Category::with('allChildren')
            ->whereNull('parent_id')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => CategoryResource::collection($categories),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $depth = 0;
        if ($request->parent_id) {
            $parent = Category::findOrFail($request->parent_id);
            $depth  = $parent->depth + 1;
        }

        $category = Category::create([
            'name'      => $request->name,
            'parent_id' => $request->parent_id,
            'depth'     => $depth,
        ]);

        return response()->json([
            'success' => true,
            'data'    => new CategoryResource($category->load('parent')),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new CategoryResource($category->load('allChildren', 'parent')),
        ]);
    }

    public function update(StoreCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->only('name', 'parent_id'));

        return response()->json([
            'success' => true,
            'data'    => new CategoryResource($category->fresh('parent')),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Catégorie supprimée.']);
    }
}