<?php

namespace App\Actions\Inventory;

use App\Enums\StockMovementType;
use App\Enums\StockTransferStatus;
use App\Models\Hr\Employee;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\StockTransfer;
use Illuminate\Support\Facades\DB;

class ShipStockTransferAction
{
    public function __construct(
        private RecordStockMovementAction $recordMovement,
    ) {}

    public function handle(
        StockTransfer $transfer,
        Employee $performedBy,
    ): StockTransfer {
        if ($transfer->status !== StockTransferStatus::Draft) {
            throw new \RuntimeException('Chỉ có thể xuất kho phiếu chuyển kho ở trạng thái Nháp.');
        }

        return DB::transaction(function () use ($transfer, $performedBy) {
            $transfer->load('items.variant', 'fromLocation');

            foreach ($transfer->items as $item) {
                // Lock in the unit cost at the time of shipping for accurate WAC accounting later
                $sourceInventory = Inventory::where('variant_id', $item->variant_id)
                    ->where('location_id', $transfer->from_location_id)
                    ->first();

                $unitCost = $sourceInventory?->cost_per_unit ? (float) $sourceInventory->cost_per_unit : null;
                $item->update(['unit_cost' => $unitCost]);

                $this->recordMovement->handle(
                    variant: $item->variant,
                    location: $transfer->fromLocation,
                    type: StockMovementType::TransferOut,
                    quantity: $item->quantity_shipped,
                    notes: "Xuất chuyển kho #{$transfer->transfer_number}",
                    performedBy: $performedBy,
                    referenceType: StockTransfer::class,
                    referenceId: $transfer->id,
                );
            }

            $transfer->update(['status' => StockTransferStatus::InTransit]);

            return $transfer->fresh();
        });
    }
}
