<?php

namespace Database\Factories\Vendor;

use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address_data' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'province' => $this->faker->state(),
            ],
            'is_active' => true,
        ];
    }
}
