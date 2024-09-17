<?php

namespace Tests\Feature;

use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_attributes()
    {
        Attribute::factory()->count(3)->create();

        $response = $this->getJson('/api/attributes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_attribute()
    {
        $attributeData = ['name' => 'Test Attribute'];

        $response = $this->postJson('/api/attributes', $attributeData);

        $response->assertStatus(201)
            ->assertJsonFragment($attributeData);
    }

    public function test_can_update_attribute()
    {
        $attribute = Attribute::factory()->create();
        $updatedData = ['name' => 'Updated Attribute'];

        $response = $this->putJson("/api/attributes/{$attribute->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment($updatedData);
    }

    public function test_can_delete_attribute()
    {
        $attribute = Attribute::factory()->create();

        $response = $this->deleteJson("/api/attributes/{$attribute->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
    }

}