<?php

namespace App\Actions\Inventory;

use App\Enums\StockMovementType;
use App\Enums\StockTransferStatus;
use App\Models\Hr\Employee;
use App\Models\Inventory\StockTransfer;
use Illuminate\Support\Facades\DB;

class ReceiveStockTransferAction
{
    public function __construct(
        private RecordStockMovementAction $recordMovement,
    ) {}

    public function handle(
        StockTransfer $transfer,
        array $receivedQuantities,
        Employee $receivedBy,
    ): StockTransfer {
        if ($transfer->status !== StockTransferStatus::InTransit) {
            throw new \RuntimeException('Chỉ có thể nhận hàng phiếu chuyển kho đang vận chuyển.');
        }

        return DB::transaction(function () use ($transfer, $receivedQuantities, $receivedBy) {
            $transfer->load('items.variant', 'toLocation', 'fromLocation');

            $itemsById = $transfer->items->keyBy('id');

            foreach ($receivedQuantities as $received) {
                $item = $itemsById->get($received['item_id']);

                if (! $item) {
                    throw new \InvalidArgumentException('Sản phẩm không thuộc phiếu chuyển kho này.');
                }

                $quantityReceived = (int) $received['quantity_received'];

                if ($quantityReceived < 0) {
                    throw new \InvalidArgumentException('Số lượng nhận không được âm.');
                }

                if ($quantityReceived > $item->quantity_shipped) {
                    throw new \InvalidArgumentException(
                        "Số lượng nhận ({$quantityReceived}) không được vượt quá số lượng xuất ({$item->quantity_shipped})."
                    );
                }

                $item->update(['quantity_received' => $quantityReceived]);

                $costPerUnit = $item->unit_cost ? (float) $item->unit_cost : null;

                if ($item->quantity_shipped > 0) {
                    // Receive the full shipped amount first to take custody and maintain WAC integrity
                    $this->recordMovement->handle(
                        variant: $item->variant,
                        location: $transfer->toLocation,
                        type: StockMovementType::TransferIn,
                        quantity: $item->quantity_shipped,
                        notes: "Nhận chuyển kho #{$transfer->transfer_number}",
                        performedBy: $receivedBy,
                        referenceType: StockTransfer::class,
                        referenceId: $transfer->id,
                        costPerUnit: $costPerUnit,
                    );
                }

                // Handle missing items (shipped but not received)
                $missingQuantity = $item->quantity_shipped - $quantityReceived;
                if ($missingQuantity > 0) {
                    // Record missing quantity as Damage at the destination location to track the loss.
                    // This correctly deducts from the location after we have received the full shipped amount.
                    $this->recordMovement->handle(
                        variant: $item->variant,
                        location: $transfer->toLocation,
                        type: StockMovementType::Damage,
                        quantity: $missingQuantity,
                        notes: "Hao hụt chuyển kho #{$transfer->transfer_number}",
                        performedBy: $receivedBy,
                        referenceType: StockTransfer::class,
                        referenceId: $transfer->id,
                    );
                }
            }

            $transfer->update([
                'status' => StockTransferStatus::Completed,
                'received_by' => $receivedBy->id,
                'received_at' => now(),
            ]);

            return $transfer->fresh();
        });
    }
}
