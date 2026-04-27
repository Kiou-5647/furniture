<?php

namespace Database\Factories\Setting;

use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use Illuminate\Database\Eloquent\Factories\Factory;

class LookupFactory extends Factory
{
    protected $model = Lookup::class;

    public function definition(): array
    {
        return [
            'namespace_id' => LookupNamespace::factory(),
            'display_name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
            'is_active' => true,
        ];
    }
}
