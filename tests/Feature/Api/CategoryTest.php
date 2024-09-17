<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');
        
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_can_create_category()
    {
        $categoryData = ['name' => 'Test Category'];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJsonFragment($categoryData);

        $this->assertDatabaseHas('categories', $categoryData);
    }

    public function test_can_update_category()
    {
        $category = Category::factory()->create();
        $updatedData = ['name' => 'Updated Category'];

        $response = $this->putJson("/api/categories/{$category->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment($updatedData);

        $this->assertDatabaseHas('categories', $updatedData);
    }

    public function test_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }





    // public function test_can_list_products_of_a_category()
    // {
    //     $category = Category::factory()->create();
    //     Product::factory()->count(3)->create(['category_id' => $category->id]);

    //     $response = $this->getJson("/api/categories/{$category->id}/products");

    //     $response->assertStatus(200)
    //         ->assertJsonFragment([
    //             'name' => $category->name,
    //             'products_count' => 3
    //         ]);
    // }


    public function test_can_list_products_of_a_category()
    {
        // Create a category
        $category = Category::factory()->create();
        
        // Create 3 products with the category_id of the created category and capture their details
        $products = Product::factory()->count(3)->create(['category_id' => $category->id]);
    
        // Send a GET request to the products endpoint for that category
        $response = $this->getJson("/api/categories/{$category->id}/products");
    
        // Assert that the response status is 200 (OK)
        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => $category->name,
            ]);
    
        // Get the response products
        $responseProducts = $response->json('products');
    
        // Assert the correct number of products is returned
        $this->assertCount(3, $responseProducts);
    
        // Assert each product matches the one created
        foreach ($products as $product) {
            $this->assertTrue(
                collect($responseProducts)->contains(function ($responseProduct) use ($product) {
                    return $responseProduct['id'] === $product->id &&
                           $responseProduct['name'] === $product->name &&
                           $responseProduct['description'] === $product->description &&
                           $responseProduct['price'] == $product->price &&
                           $responseProduct['stock_quantity'] === $product->stock_quantity &&
                           $responseProduct['image_url'] === $product->image_url;
                })
            );
        }
    }
    


}