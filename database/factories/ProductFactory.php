<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'category' => $this->faker->randomElement(['vegetable', 'fruits']),
            'price_per_unit' => $this->faker->numberBetween(10, 100),
            'unit' => $this->faker->randomElement(['kg', 'sacks', 'pcs']),
            'description' => Str::random(10),
            'image' => 'https://media.istockphoto.com/id/544652720/photo/japanese-pumpkin-kabocha-on-a-white-background.jpg?s=612x612&w=0&k=20&c=pFvYZsLfMN-B9fQU9PuhyEjt_PJp9pCxrYU_-NZiXIM=',
            'estimated_harvest_date' => now(),
            'stocks' => $this->faker->numberBetween(10,100)
        ];
    }
}
