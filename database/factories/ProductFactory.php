<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'image_url' => $this->faker->imageUrl(),
            'category_id' => Category::factory(),
        ];
    }

    public function withAttributes(array $attributes)
    {
        return $this->state(function (array $attributes) {
            return [
                'attributes' => $attributes,
            ];
        });
    }
}
