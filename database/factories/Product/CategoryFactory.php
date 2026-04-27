<?php

namespace Database\Factories\Product;

use App\Enums\ProductType;
use App\Models\Product\Category;
use App\Models\Setting\Lookup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'group_id' => Lookup::factory(),
            'display_name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->sentence(),
            'product_type' => ProductType::Furniture, // Or use ProductType enum
            'is_active' => true,
        ];
    }
}
