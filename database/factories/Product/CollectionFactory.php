<?php

namespace Database\Factories\Product;

use App\Models\Product\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'display_name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(),
            'is_active' => true,
            'is_featured' => false,
        ];
    }
}
