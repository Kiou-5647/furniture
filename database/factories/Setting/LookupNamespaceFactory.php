<?php

namespace Database\Factories\Setting;

use App\Models\Setting\LookupNamespace;
use Illuminate\Database\Eloquent\Factories\Factory;

class LookupNamespaceFactory extends Factory
{
    protected $model = LookupNamespace::class;

    public function definition(): array
    {
        return [
            'display_name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'is_active' => true,
        ];
    }
}
