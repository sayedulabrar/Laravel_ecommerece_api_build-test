<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductUnitTest extends TestCase
{
    use RefreshDatabase;
        // New Tests for Edge Cases
        public function test_prevents_creating_duplicate_product_with_same_attributes()
        {
            $category = Category::factory()->create();
            $attribute = Attribute::factory()->create();
    
            // Create an initial product
            $existingProductData = [
                'name' => 'Existing Product',
                'description' => 'Existing product description',
                'price' => 100.50,
                'stock_quantity' => 10,
                'category_id' => $category->id,
                'attributes' => [
                    ['id' => $attribute->id, 'value' => 'Value 1']
                ],
            ];
    
            $this->postJson('/api/products', $existingProductData);
    
            // Try creating another product with the same data
            $duplicateProductData = [
                'name' => 'Existing Product',
                'description' => 'Existing product description',
                'price' => 100.50,
                'stock_quantity' => 5,
                'category_id' => $category->id,
                'attributes' => [
                    ['id' => $attribute->id, 'value' => 'Value 1']
                ],
            ];
    
            $response = $this->postJson('/api/products', $duplicateProductData);
    
            // Assert that the product stock quantity has been increased
            $response->assertStatus(200) // Assuming it updates the quantity if duplicate found
                     ->assertJsonFragment(['stock_quantity' => 15]);
    
            $this->assertDatabaseHas('products', [
                'name' => 'Existing Product',
                'stock_quantity' => 15
            ]);
        }
    
        public function test_prevents_updating_product_to_duplicate_existing_product()
        {
            $category = Category::factory()->create();
            $attribute1 = Attribute::factory()->create();
            $attribute2 = Attribute::factory()->create();
    
            // Create two products
            $product1 = Product::factory()->create([
                'name' => 'Product 1',
                'description' => 'Product 1 description',
                'price' => 100,
                'stock_quantity' => 10,
                'category_id' => $category->id,
            ]);
            $product1->attributes()->sync([$attribute1->id => ['value' => 'Value 1']]);
    
            $product2 = Product::factory()->create([
                'name' => 'Product 2',
                'description' => 'Product 2 description',
                'price' => 150,
                'stock_quantity' => 5,
                'category_id' => $category->id,
            ]);
            $product2->attributes()->sync([$attribute2->id => ['value' => 'Value 2']]);
    
            // Try updating product 2 to be identical to product 1
            $duplicateData = [
                'name' => 'Product 1',
                'description' => 'Product 1 description',
                'price' => 100,
                'stock_quantity' => 10,
                'category_id' => $category->id,
                'attributes' => [
                    ['id' => $attribute1->id, 'value' => 'Value 1']
                ],
            ];
    
            $response = $this->putJson("/api/products/{$product2->id}", $duplicateData);
    
            // Assert the update fails due to duplication
            $response->assertStatus(400)
                     ->assertJsonFragment(['error' => 'Another product with the same name, description, price, category, and attributes already exists. Please change the values.']);
        }

        
}
