<?php

namespace Database\Seeders;

use App\Models\Fulfillment\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedShippingMethods();
    }

    protected function seedShippingMethods(): void
    {
        ShippingMethod::firstOrCreate(
            ['code' => 'standard'],
            [
                'name' => 'Tiêu chuẩn',
                'code' => 'standard',
                'price' => 50000,
                'estimated_delivery_days' => 7,
                'is_active' => true,
            ]
        );

        ShippingMethod::firstOrCreate(
            ['code' => 'express'],
            [
                'name' => 'Nhanh',
                'code' => 'express',
                'price' => 100000,
                'estimated_delivery_days' => 3,
                'is_active' => true,
            ]
        );

        $this->command->info('Created 2 shipping methods.');
    }
}
