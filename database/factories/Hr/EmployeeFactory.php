<?php

namespace Database\Factories\Hr;

use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\Auth\User::factory()->state(['type' => 'employee']),
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'department_id' => Department::inRandomOrder()->first()?->id,
            'location_id' => Location::inRandomOrder()->first()?->id,
            'hire_date' => fake()->date(),
        ];
    }
}
