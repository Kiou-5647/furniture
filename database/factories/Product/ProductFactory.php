<?php

namespace Database\Factories\Product;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'name' => $name,
            'status' => 'published',
            'min_price' => 0,
            'max_price' => 0,
            'features' => [],
            'specifications' => [],
            'option_groups' => [],
            'filterable_options' => [],
            'care_instructions' => [],
            'assembly_info' => [],
            'is_new_arrival' => fake()->boolean(),
            'views_count' => fake()->numberBetween(0, 1000),
            'reviews_count' => fake()->numberBetween(0, 50),
            'average_rating' => fake()->randomFloat(2, 0, 5),
        ];
    }
}
