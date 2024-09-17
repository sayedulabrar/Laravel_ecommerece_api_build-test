<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Http\Resources\AttributeResource;
use Illuminate\Validation\ValidationException;

class AttributeController extends Controller
{
    public function index()
    {
        return AttributeResource::collection(Attribute::all());
    }

    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:attributes',
            ], [
                'name.unique' => 'The Attribute name must be unique.',
            ]);

            // Create the attribute with the validated data
            $attribute = Attribute::create($validatedData);

            // Return the created attribute as a resource with a 201 status
            return response()->json(new AttributeResource($attribute), 201);

        } catch (ValidationException $e) {
            // Return a JSON response with validation errors and a 422 status
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function show(Attribute $attribute)
    {
        return new AttributeResource($attribute);
    }

    public function update(Request $request, Attribute $attribute)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:attributes,name,' . $attribute->id,
            ], [
                'name.unique' => 'The Attribute name must be unique.',
            ]);

            // Update the attribute with the validated data
            $attribute->update($validatedData);

            // Return the updated attribute as a resource
            return response()->json(new AttributeResource($attribute), 200);

        } catch (ValidationException $e) {
            // Return a JSON response with validation errors and a 422 status
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } 
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return response()->json(null, 204);
    }
}