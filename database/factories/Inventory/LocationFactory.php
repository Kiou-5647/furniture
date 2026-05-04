<?php

namespace Database\Factories\Inventory;

use App\Enums\LocationType;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use App\Models\Setting\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;
    protected static $provinces = null;

    public function definition(): array
    {
        if (static::$provinces === null) {
            static::$provinces = Province::all();
        }

        $province = static::$provinces->random();
        $ward = $province->wards()->inRandomOrder()->first();

        $type = $this->faker->randomElement(LocationType::cases());

        return [
            'code' => Location::generateCode($type->value),
            'name' => $this->faker->company() . ' ' . $type->label(),
            'type' => $type,
            'province_code' => $province?->province_code,
            'province_name' => $province?->name,
            'ward_code' => $ward?->ward_code,
            'ward_name' => $ward?->name,
            'street' => fake()->streetAddress(),
            'phone' => $this->faker->phoneNumber(),
            'is_active' => true,
            'manager_id' => null,
            'created_at' => $this->faker->dateTimeBetween('-2 months', '-1 months'),
            'updated_at' => $this->faker->dateTimeBetween('-1 months', 'now'),
        ];
    }

    /**
     * State for a specific location type.
     */
    public function warehouse(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => LocationType::Warehouse,
            'code' => Location::generateCode(LocationType::Warehouse->value),
            'name' => 'Kho ' . $this->faker->company(),
        ]);
    }

    public function retail(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => LocationType::Retail,
            'code' => Location::generateCode(LocationType::Retail->value),
            'name' => 'Cửa hàng ' . $this->faker->company(),
        ]);
    }
}
