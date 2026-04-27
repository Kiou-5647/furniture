<?php

namespace Database\Factories\Hr;

use App\Models\Hr\Designer;
use App\Models\Hr\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Designer>
 */
class DesignerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'user_id' => fn(array $attributes) => Employee::find($attributes['employee_id'])->user_id,
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'hourly_rate' => fake()->randomElement([150000, 200000, 250000]),
            'is_active' => true,
        ];
    }
}
