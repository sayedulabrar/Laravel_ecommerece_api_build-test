<?php

namespace Tests\Feature;

use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_attribute_with_duplicate_name()
{
    // Create an attribute with a specific name
    $existingAttribute = Attribute::factory()->create(['name' => 'Unique Attribute Name']);

    // Attempt to create another attribute with the same name
    $response = $this->postJson('/api/attributes', ['name' => 'Unique Attribute Name']);

    // Assert that the response status is 422 Unprocessable Entity
    $response->assertStatus(422)
        ->assertJson([
            'errors' => [
                'name' => [
                    'The Attribute name must be unique.'
                ]
            ]
        ]);

    // Assert that the database still contains only one attribute with the unique name
    $this->assertDatabaseCount('attributes', 1);
}


    public function test_cannot_update_attribute_with_duplicate_name()
    {
        // Create two categories with different names
        $category1 = Attribute::factory()->create(['name' => 'Attribute One']);
        $category2 = Attribute::factory()->create(['name' => 'Attribute Two']);
    
        // Attempt to update the second category to have the same name as the first
        $response = $this->putJson("/api/attributes/{$category2->id}", ['name' => 'Attribute One']);
    
        // Assert that the response status is 422 Unprocessable Entity
        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'name' => [
                        'The Attribute name must be unique.'
                    ]
                ]
            ]);
        
        // Assert that the name of the second category was not changed
        $this->assertDatabaseHas('attributes', ['id' => $category2->id, 'name' => 'Attribute Two']);
    }
    

    public function test_cannot_update_attribute_with_non_existent_id()
    {
        // Attempt to update a product with a non-existent ID
        $response = $this->putJson('/api/attributes/9999', [
            'name' => 'Non-Existent Attribute', 
        ]);

        // Assert that the response status is 404 Not Found
        $response->assertStatus(404)
            ->assertJson([
                'error' => 'The requested Content was not found in Database.'
            ]);
    }
}