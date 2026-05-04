<?php

namespace Database\Factories\Inventory;

use App\Enums\StockTransferStatus;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockTransferFactory extends Factory
{
    protected $model = StockTransfer::class;

    public function definition(): array
    {
        return [
            'transfer_number' => 'ST-' . fake()->unique()->bothify('??###-####'),
            'status' => fake()->randomElement(StockTransferStatus::cases()),
            'from_location_id' => Location::factory(),
            'to_location_id' => Location::factory(),
            'requested_by' => Employee::factory(),
            'notes' => fake()->sentence(),
        ];
    }
}
