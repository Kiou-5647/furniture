<?php

namespace Database\Factories\Product;

use App\Models\Product\Bundle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BundleFactory extends Factory
{
    protected $model = Bundle::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'discount_type' => fake()->randomElement(['percentage', 'fixed_amount', 'fixed_price']),
            'discount_value' => fake()->randomFloat(2, 5, 50),
            'is_active' => true,
            'views_count' => fake()->numberBetween(0, 1000),
            'reviews_count' => fake()->numberBetween(0, 100),
            'average_rating' => fake()->randomFloat(2, 0, 5),
        ];
    }
}
