<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:categories',
            ], [
                'name.unique' => 'The Category name must be unique.',
            ]);
    
            // Create the category only if validation passes
            $category = Category::create($validatedData);
    
            // Return a JSON response with the created category and a 201 status
            return response()->json(new CategoryResource($category), 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return a JSON response with validation errors and a 422 status
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }
    

    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            ], [
                'name.unique' => 'The Category name must be unique.',
            ]);

            // Update the category with the validated data
            $category->update($validatedData);

            // Return the updated category with a 200 OK status
            return response()->json(new CategoryResource($category), 200);

        } catch (ValidationException $e) {
            // Return a JSON response with validation errors and a 422 status
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } 
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }

    // // New method to fetch products of a specific category
    // public function getCategoryProductsCount($id)
    // {
    //     // Fetch the category by id
    //     $category = Category::find($id);

    //     // Check if the category exists
    //     if (!$category) {
    //         return response()->json(['error' => 'Category not found'], 404);
    //     }

    //     // Count products with category_id == $id
    //     $productCount = Product::where('category_id', $id)->count();

    //     // Return the product count along with the category name
    //     return response()->json([
    //         'name' => $category->name,
    //         'products_count' => $productCount
    //     ], 200);
    // }

        // New method to fetch products of a specific category
        public function getCategoryProducts($id)
        {
            // Fetch the category by id
            $category = Category::find($id);
    
            // Check if the category exists
            if (!$category) {
                return response()->json(['error' => 'Category not found'], 404);
            }
    
            // Count products with category_id == $id
            $products = Product::with(['category', 'attributes'])->where('category_id', $id)->get();
    
            // Return the product count along with the category name
            return response()->json([
                'name' => $category->name,
                'products' => $products
            ], 200);
        }
}
