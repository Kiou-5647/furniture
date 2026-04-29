<?php

namespace Database\Factories\Customer;

use App\Models\Auth\User;
use App\Models\Model;
use App\Models\Setting\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class CustomerFactory extends Factory
{
    protected static $provinces = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (static::$provinces === null) {
            static::$provinces = Province::all();
        }

        $province = static::$provinces->random();
        $ward = $province->wards()->inRandomOrder()->first();

        return [
            'user_id' => User::factory()->state(['type' => 'customer']),
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'province_code' => $province?->province_code,
            'province_name' => $province?->name,
            'ward_code' => $ward?->ward_code,
            'ward_name' => $ward?->name,
            'street' => fake()->streetAddress(),
            'total_spent' => 0,
        ];
    }
}
