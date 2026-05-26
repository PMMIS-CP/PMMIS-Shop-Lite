<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['fa' => fake()->name(), 'en' => fake()->name()],
            'price_usd' => fake()->randomFloat(2, 1, 100),
            'category_id' => \App\Models\Category::factory(),
            'sku' => 'SKU-' . fake()->unique()->numerify('#####'),
            'is_active' => true,
            'stock' => 10,
        ];
    }
}
