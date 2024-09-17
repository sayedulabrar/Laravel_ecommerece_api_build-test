<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;

class CategoryUnitTest extends TestCase
{
    use RefreshDatabase;


    public function test_cannot_create_category_with_duplicate_name()
{
    // Create a category with a specific name
    $existingCategory = Category::factory()->create(['name' => 'Unique Category Name']);

    // Attempt to create another category with the same name
    $response = $this->postJson('/api/categories', ['name' => 'Unique Category Name']);

    // Assert that the response status is 422 Unprocessable Entity
    $response->assertStatus(422)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The Category name must be unique.'
                ]
            ]
        ]);

    // Assert that the database still contains only one category with the unique name
    $this->assertDatabaseCount('categories', 1);
}

    public function test_cannot_update_category_with_duplicate_name()
    {
        // Create two categories with different names
        $category1 = Category::factory()->create(['name' => 'Category One']);
        $category2 = Category::factory()->create(['name' => 'Category Two']);
    
        // Attempt to update the second category to have the same name as the first
        $response = $this->putJson("/api/categories/{$category2->id}", ['name' => 'Category One']);
    
        // Assert that the response status is 422 Unprocessable Entity
        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'name' => [
                        'The Category name must be unique.'
                    ]
                ]
            ]);
        
        // Assert that the name of the second category was not changed
        $this->assertDatabaseHas('categories', ['id' => $category2->id, 'name' => 'Category Two']);
    }
    

    public function test_cannot_update_product_with_non_existent_id()
    {
        // Attempt to update a product with a non-existent ID
        $response = $this->putJson('/api/products/9999', [
            'name' => 'Non-Existent Category', 
        ]);

        // Assert that the response status is 404 Not Found
        $response->assertStatus(404)
            ->assertJson([
                'error' => 'The requested Content was not found in Database.'
            ]);
    }


}