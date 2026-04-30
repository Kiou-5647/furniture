<?php

namespace Database\Seeders;

use App\Actions\Inventory\RecordStockMovementAction;
use App\Enums\StockMovementType;
use App\Models\Auth\User;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Seeder;

class InitialStockSeeder extends Seeder
{
    public function run(): void
    {
        $recordMovement = app(RecordStockMovementAction::class);
        $locations = Location::all();
        $variants = ProductVariant::all();
        $superAdmin = User::whereHas('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->first();

        if (!$superAdmin || !$superAdmin->employee) {
            $this->command->error('No employee found to act as auditor.');
            return;
        }

        $quantities = [0, 0, 0, 0, 25, 50];

        foreach ($variants as $variant) {
            foreach ($locations as $location) {
                // 1. Determine random quantity
                $qty = collect($quantities)->random();
                if ($qty === 0) continue;

                // 2. Calculate the base cost based on profit margin
                $cost = $this->calculateBaseCost($variant);

                // 3. Apply randomization if price >= 5,000,000
                if ($variant->price >= 5000000) {
                    $offset = rand(-1000000, 1000000);
                    $cost += $offset;
                }

                // Ensure cost doesn't go below zero
                $cost = max(0, $cost);

                // 4. Execute via Action to ensure Inventory and StockMovement are handled
                $recordMovement->handle(
                    variant: $variant,
                    location: $location,
                    type: StockMovementType::TransferIn,
                    quantity: $qty,
                    notes: 'Nhập kho ban đầu cho hệ thống',
                    performedBy: $superAdmin->employee,
                    costPerUnit: $cost,
                    forceUpdatePrice: false
                );
            }
        }
    }

    protected function calculateBaseCost($variant): float
    {
        $price = (float) $variant->price;
        $margin = (float) $variant->profit_margin_value;

        if ($variant->profit_margin_unit === 'percentage') {
            // Cost = Price / (1 + margin%)
            return $price / (1 + ($margin / 100));
        }

        // Cost = Price - fixed margin
        return $price - $margin;
    }
}
