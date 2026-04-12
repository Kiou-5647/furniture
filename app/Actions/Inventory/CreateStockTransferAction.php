<?php

namespace App\Actions\Inventory;

use App\Enums\StockTransferStatus;
use App\Models\Hr\Employee;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockTransfer;
use Illuminate\Support\Facades\DB;

class CreateStockTransferAction
{
    public function handle(
        Location $fromLocation,
        Location $toLocation,
        array $items,
        ?Employee $requestedBy = null,
        ?string $notes = null,
    ): StockTransfer {
        if ($fromLocation->id === $toLocation->id) {
            throw new \InvalidArgumentException('Vị trí nguồn và đích không được trùng nhau.');
        }

        if (empty($items)) {
            throw new \InvalidArgumentException('Phiếu chuyển kho phải có ít nhất một sản phẩm.');
        }

        return DB::transaction(function () use ($fromLocation, $toLocation, $items, $requestedBy, $notes) {
            $transfer = StockTransfer::create([
                'transfer_number' => StockTransfer::generateTransferNumber(),
                'from_location_id' => $fromLocation->id,
                'to_location_id' => $toLocation->id,
                'status' => StockTransferStatus::Draft,
                'requested_by' => $requestedBy?->id,
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                $inventory = Inventory::where('variant_id', $item['variant_id'])
                    ->where('location_id', $fromLocation->id)
                    ->first();

                if (! $inventory || $inventory->quantity < $item['quantity']) {
                    $available = $inventory?->quantity ?? 0;
                    throw new \RuntimeException(
                        "Không đủ tồn kho tại vị trí nguồn. Hiện tại: {$available}, Yêu cầu: {$item['quantity']}"
                    );
                }

                $transfer->items()->create([
                    'variant_id' => $item['variant_id'],
                    'quantity_shipped' => $item['quantity'],
                    'quantity_received' => 0,
                ]);
            }

            return $transfer->load('items');
        });
    }
}
