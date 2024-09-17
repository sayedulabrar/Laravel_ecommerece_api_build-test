<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_can_create_product()
    {
        $category = Category::factory()->create();
        $attribute = Attribute::factory()->create();
        $data = [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 100.50,
            'stock_quantity' => 10,
            'category_id' => $category->id,
            'attributes' => [
                ['id' => $attribute->id, 'value' => 'Value 1']
            ],
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'New Product']);

        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }

    public function test_can_show_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $product->name]);
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create();
        $newData = [
            'name' => 'Updated Product',
            'price' => 150.75,
            'stock_quantity' => 20
        ];

        $response = $this->putJson("/api/products/{$product->id}", $newData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Product']);

        $this->assertDatabaseHas('products', ['name' => 'Updated Product']);
    }

    public function test_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

        
}
