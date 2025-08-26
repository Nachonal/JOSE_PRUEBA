<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $cats = ['electronics','clothing','food','otros'];
        return [
            'code' => strtoupper(fake()->unique()->bothify('PRD-####')),
            'name' => fake()->unique()->words(3, true),
            'category' => fake()->randomElement($cats),
            'price' => fake()->randomFloat(2, 1, 9999),
            'stock' => fake()->numberBetween(0, 500),
            'is_active' => fake()->boolean(85),
            'image_url' => fake()->boolean(50) ? fake()->url() : null,
        ];
    }
}
