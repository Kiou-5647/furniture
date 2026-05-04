<?php

namespace App\Actions\Inventory;

use App\Enums\StockMovementType;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use App\Models\Product\ProductVariant;
use Illuminate\Support\Facades\DB;

class BulkImportStockAction
{
    public function __construct(
        private RecordStockMovementAction $recordStockMovement,
    ) {}

    /**
     * Handle bulk import of stock variants into a location.
     *
     * @param Location $location
     * @param Employee $performedBy
     * @param array $items List of items to import: [['variant_id' => ..., 'quantity' => ..., 'cost_per_unit' => ..., 'notes' => ...], ...]
     * @return array List of created stock movements
     * @throws \Exception
     */
    public function handle(Location $location, Employee $performedBy, array $items): array
    {
        return DB::transaction(function () use ($location, $performedBy, $items) {
            $movements = [];

            foreach ($items as $item) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                
                $movements[] = $this->recordStockMovement->handle(
                    $variant,
                    $location,
                    StockMovementType::Receive,
                    (int) $item['quantity'],
                    $item['notes'] ?? null,
                    $performedBy,
                    null,
                    null,
                    isset($item['cost_per_unit']) ? (float) $item['cost_per_unit'] : null,
                    false, // Default to false for bulk imports, or make it configurable
                );
            }

            return $movements;
        });
    }
}
