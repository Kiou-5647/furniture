<?php

namespace Database\Factories\Product;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product\Product::factory(),
            'sku' => fake()->unique()->bothify('SKU-####-????'), // SKU is required and unique
            'status' => 'active',
            'name' => fake()->words(2, true),
            'price' => fake()->randomFloat(2, 100000, 10000000),
            'profit_margin_unit' => 'fixed',
            'weight' => ['value' => fake()->randomFloat(2, 1, 50), 'unit' => 'kg'],
            'dimensions' => ['length' => 10, 'width' => 10, 'height' => 10, 'unit' => 'cm'],
            'option_values' => [],
            'features' => [],
            'specifications' => [],
            'care_instructions' => [],
            'views_count' => fake()->numberBetween(0, 500),
            'reviews_count' => fake()->numberBetween(0, 20),
            'average_rating' => fake()->randomFloat(2, 0, 5),
        ];
    }
}
