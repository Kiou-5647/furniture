<?php

namespace Database\Seeders;

use App\Models\Inventory\Location;
use Illuminate\Database\Seeder;

class DefaultLocationSeeder extends Seeder
{
    public function run(): void
    {
        if (Location::count() > 0) {
            return;
        }

        Location::create([
            'code' => 'LOC-WH-001',
            'name' => 'Kho chính',
            'type' => 'warehouse',
            'is_active' => true,
            'address_data' => [],
        ]);
    }
}
