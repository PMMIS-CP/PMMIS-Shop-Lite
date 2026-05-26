<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => [
                'fa' => $this->faker->word,
                'en' => $this->faker->word
            ],
            'slug' => [
                'fa' => $this->faker->slug,
                'en' => $this->faker->slug
            ],
            'is_active' => true,
        ];
    }
}