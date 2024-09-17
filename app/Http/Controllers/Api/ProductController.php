<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(Product::with(['category', 'attributes'])->get());
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'category_id' => 'required|exists:categories,id',
            'attributes' => 'array',
            'attributes.*.id' => 'required|exists:attributes,id',
            'attributes.*.value' => 'required|string',
        ], [
            'category_id.exists' => 'The selected category does not exist.',
            'attributes.*.id.exists' => 'One or more of the selected attributes are invalid.',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Check if a product with the same name, description, price, and category exists
            $existingProduct = Product::where('name', $validatedData['name'])
                ->where('description', $validatedData['description'])
                ->where('price', $validatedData['price'])
                ->where('category_id', $validatedData['category_id'])
                ->first();
    
            Log::info('Existing Product:', ['product' => $existingProduct]);
    
            if ($existingProduct) {
                // Check if the attributes match exactly
                $attributesMatch = true;
    
                if (isset($validatedData['attributes'])) {
                    $inputAttributes = collect($validatedData['attributes'])->mapWithKeys(function ($item) {
                        return [$item['id'] => $item['value']];
                    });
    
                    $existingAttributes = $existingProduct->attributes->mapWithKeys(function ($item) {
                        return [$item->id => $item->pivot->value];
                    });
    
                    Log::info('Attributes Match:', ['inputAttributes' => $inputAttributes, 'existingAttributes' => $existingAttributes]);
    
                    // Compare the two attribute sets
                    if ($inputAttributes->count() !== $existingAttributes->count() || !$inputAttributes->diffAssoc($existingAttributes)->isEmpty()) {
                        $attributesMatch = false;
                    }
                }
    
                Log::info('Checking Attributes Match:', [$attributesMatch]);
    
                // If the attributes match, update the stock quantity
                if ($attributesMatch) {
                    $existingProduct->update([
                        'stock_quantity' => $existingProduct->stock_quantity + $validatedData['stock_quantity'],
                    ]);
    
                    DB::commit();
                    return response()->json([
                        'message' => 'Product already exists with the same attributes. Stock quantity updated.',
                        'product' => new ProductResource($existingProduct->load(['category', 'attributes']))
                    ], 200);
                }
            }
    
            // Create new product if no existing match is found
            $product = Product::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'stock_quantity' => $validatedData['stock_quantity'],
                'image_url' => $validatedData['image_url'] ?? null,
                'category_id' => $validatedData['category_id'],
            ]);
    
            if (isset($validatedData['attributes'])) {
                $attributes = collect($validatedData['attributes'])->mapWithKeys(function ($item) {
                    return [$item['id'] => ['value' => $item['value']]];
                });
                $product->attributes()->sync($attributes);
            }
    
            DB::commit();
    
            return new ProductResource($product->load(['category', 'attributes']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create product', 'message' => $e->getMessage()], 500);
        }
    }
    
    

    public function show(Product $product)
    {
        return new ProductResource($product->load(['category', 'attributes']));
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'image_url' => 'sometimes|nullable|url',
            'category_id' => 'sometimes|required|exists:categories,id',
            'attributes' => 'sometimes|array',
            'attributes.*.id' => 'required_with:attributes|exists:attributes,id',
            'attributes.*.value' => 'required_with:attributes|string',
        ], [
            'category_id.exists' => 'The selected category does not exist.',
            'attributes.*.id.exists' => 'One or more of the selected attributes are invalid.',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Check if the update would result in a product that matches another existing product
            $query = Product::where('id', '!=', $product->id)
                ->where('name', $validatedData['name'] ?? $product->name)
                ->where('description', $validatedData['description'] ?? $product->description)
                ->where('price', $validatedData['price'] ?? $product->price)
                ->where('category_id', $validatedData['category_id'] ?? $product->category_id);
    
            // If attributes are provided in the update, check if another product has the same attributes
            if (isset($validatedData['attributes'])) {
                $inputAttributes = collect($validatedData['attributes'])->mapWithKeys(function ($item) {
                    return [$item['id'] => $item['value']];
                });
    
                // Retrieve products that have the same attributes
                $matchingProducts = $query->get();
                foreach ($matchingProducts as $matchingProduct) {
                    $existingAttributes = $matchingProduct->attributes->mapWithKeys(function ($item) {
                        return [$item->id => $item->pivot->value];
                    });
    
                    // Check if attribute counts match
                    if ($inputAttributes->count() === $existingAttributes->count()) {
                        // Check if all attributes match
                        $attributesMatch = true;
                        foreach ($inputAttributes as $key => $value) {
                            if ($existingAttributes->get($key) !== $value) {
                                $attributesMatch = false;
                                break;
                            }
                        }
    
                        // If all attributes match, return an error
                        if ($attributesMatch) {
                            return response()->json([
                                'error' => 'Another product with the same name, description, price, category, and attributes already exists. Please change the values.'
                            ], 400);
                        }
                    }
                }
            }
    
            // Perform the product update
            $product->update($validatedData);
    
            // Update the attributes if provided
            if (isset($validatedData['attributes'])) {
                $attributes = collect($validatedData['attributes'])->mapWithKeys(function ($item) {
                    return [$item['id'] => ['value' => $item['value']]];
                });
                $product->attributes()->sync($attributes);
            }
    
            DB::commit();
    
            return new ProductResource($product->load(['category', 'attributes']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update product', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}