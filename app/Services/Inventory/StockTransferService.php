<?php

namespace App\Services\Inventory;

use App\Actions\Inventory\RecordStockMovementAction;
use App\Enums\StockMovementType;
use App\Enums\StockTransferStatus;
use App\Models\Employee\Employee;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockTransfer;
use App\Models\Inventory\StockTransferItem;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    public function __construct(
        private RecordStockMovementAction $recordStockMovement,
    ) {}

    /**
     * Create a transfer draft.
     *
     * @param  Location  $fromLocation  Source location
     * @param  Location  $toLocation  Destination location
     * @param  array  $items  Array of items with variant_id and quantity
     * @param  Employee|null  $requestedBy  Employee who requested the transfer
     * @param  string|null  $notes  Optional notes
     * @return StockTransfer The created transfer
     */
    public function createTransfer(
        Location $fromLocation,
        Location $toLocation,
        array $items,
        ?Employee $requestedBy = null,
        ?string $notes = null,
    ): StockTransfer {
        if ($fromLocation->id === $toLocation->id) {
            throw new \InvalidArgumentException('Kho nguồn và kho đích không được trùng nhau');
        }

        if (empty($items)) {
            throw new \InvalidArgumentException('Danh sách sản phẩm chuyển kho không được để trống');
        }

        return DB::transaction(function () use ($fromLocation, $toLocation, $items, $requestedBy, $notes) {
            $transfer = StockTransfer::create([
                'from_location_id' => $fromLocation->id,
                'to_location_id' => $toLocation->id,
                'requested_by' => $requestedBy?->id,
                'notes' => $notes,
                'status' => StockTransferStatus::Draft,
            ]);

            foreach ($items as $item) {
                StockTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'variant_id' => $item['variant_id'],
                    'quantity_shipped' => $item['quantity'] ?? 1,
                    'quantity_received' => 0,
                ]);
            }

            return $transfer;
        });
    }

    /**
     * Ship transfer - creates transfer_out movements.
     *
     * @param  StockTransfer  $transfer  The transfer to ship
     * @param  Employee  $performedBy  Employee who ships the transfer
     */
    public function shipTransfer(StockTransfer $transfer, Employee $performedBy): void
    {
        if (! $transfer->status->canBeShipped()) {
            throw new \RuntimeException(
                "Không thể vận chuyển. Trạng thái hiện tại: {$transfer->status->label()}"
            );
        }

        DB::transaction(function () use ($transfer, $performedBy) {
            // Update transfer status
            $transfer->status = StockTransferStatus::InTransit;
            $transfer->save();

            // Process each item
            foreach ($transfer->items as $item) {
                $variant = $item->variant;
                $fromLocation = $transfer->fromLocation;
                $quantity = $item->quantity_shipped;

                // Skip if product is dropship or custom made
                $product = $variant->product;
                if ($product && ($product->is_dropship || $product->is_custom_made)) {
                    continue;
                }

                // Update quantity shipped
                $item->quantity_shipped = $quantity;
                $item->save();

                // Create transfer_out movement
                $this->recordStockMovement->handle(
                    variant: $variant,
                    location: $fromLocation,
                    type: StockMovementType::TransferOut,
                    quantity: $quantity,
                    notes: "Chuyển kho: {$transfer->transfer_number}",
                    performedBy: $performedBy,
                    referenceType: StockTransfer::class,
                    referenceId: $transfer->id,
                );
            }
        });
    }

    /**
     * Receive transfer - creates transfer_in movements.
     *
     * @param  StockTransfer  $transfer  The transfer to receive
     * @param  Employee  $performedBy  Employee who receives the transfer
     */
    public function receiveTransfer(StockTransfer $transfer, Employee $performedBy): void
    {
        if (! $transfer->status->canBeReceived()) {
            throw new \RuntimeException(
                "Không thể nhận hàng. Trạng thái hiện tại: {$transfer->status->label()}"
            );
        }

        DB::transaction(function () use ($transfer, $performedBy) {
            // Update transfer status
            $transfer->status = StockTransferStatus::Received;
            $transfer->received_by = $performedBy->id;
            $transfer->received_at = now();
            $transfer->save();

            // Process each item
            foreach ($transfer->items as $item) {
                $variant = $item->variant;
                $toLocation = $transfer->toLocation;
                $quantity = $item->quantity_shipped;

                // Skip if product is dropship or custom made
                $product = $variant->product;
                if ($product && ($product->is_dropship || $product->is_custom_made)) {
                    continue;
                }

                // Update quantity received
                $item->quantity_received = $quantity;
                $item->save();

                // Create transfer_in movement
                $this->recordStockMovement->handle(
                    variant: $variant,
                    location: $toLocation,
                    type: StockMovementType::TransferIn,
                    quantity: $quantity,
                    notes: "Nhận chuyển kho: {$transfer->transfer_number}",
                    performedBy: $performedBy,
                    referenceType: StockTransfer::class,
                    referenceId: $transfer->id,
                );
            }
        });
    }

    /**
     * Cancel transfer.
     *
     * @param  StockTransfer  $transfer  The transfer to cancel
     */
    public function cancelTransfer(StockTransfer $transfer): void
    {
        if (! $transfer->status->canBeCancelled()) {
            throw new \RuntimeException(
                "Không thể hủy. Trạng thái hiện tại: {$transfer->status->label()}"
            );
        }

        DB::transaction(function () use ($transfer) {
            $transfer->status = StockTransferStatus::Cancelled;
            $transfer->save();
        });
    }
}
