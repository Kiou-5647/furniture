<?php

namespace Database\Factories\Inventory;

use App\Enums\StockMovementType;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockMovement;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'variant_id' => ProductVariant::factory(),
            'location_id' => Location::factory(),
            'performed_by' => Employee::factory(),
            'type' => fake()->randomElement(StockMovementType::cases()),
            'quantity' => fake()->numberBetween(1, 100),
            'quantity_before' => fake()->numberBetween(0, 1000),
            'quantity_after' => fake()->numberBetween(0, 1000),
            'cost_per_unit' => fake()->randomFloat(2, 10, 1000),
            'cost_per_unit_before' => fake()->randomFloat(2, 10, 1000),
            'notes' => fake()->sentence(),
        ];
    }
}
