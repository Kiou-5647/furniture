<?php

namespace Database\Seeders;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Product\Product;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedTimberInventory();
    }

    protected function seedTimberInventory(): void
    {
        $timber = Product::where('name', 'Ghế Timber')->first();
        if (! $timber) {
            return;
        }

        $stock = [
            [10, 8, 25, 15],
            [5, 3, 12, 0],
            [7, 5, 20, 10],
            [0, 0, 30, 18],
            [6, 4, 15, 8],
            [12, 10, 35, 22],
            [0, 0, 0, 0],
        ];

        $locations = Location::where('is_active', true)->orderBy('code')->get();

        foreach ($timber->variants as $vIdx => $variant) {
            $s = $stock[$vIdx] ?? [0, 0, 0, 0];
            foreach ($locations as $lIdx => $loc) {
                $qty = $s[$lIdx] ?? 0;
                if ($qty > 0) {
                    Inventory::firstOrCreate(
                        ['variant_id' => $variant->id, 'location_id' => $loc->id],
                        [
                            'variant_id' => $variant->id,
                            'location_id' => $loc->id,
                            'quantity' => $qty,
                            'cost_per_unit' => $variant->price * 0.55,
                        ]
                    );
                }
            }
        }

        $this->command->info('Added inventory for Ghế Timber.');
    }
}
