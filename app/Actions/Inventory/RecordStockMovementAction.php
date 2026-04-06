<?php

namespace App\Actions\Inventory;

use App\Enums\StockMovementType;
use App\Models\Employee\Employee;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\StockValuation;
use App\Models\Product\ProductVariant;
use Illuminate\Support\Facades\DB;

class RecordStockMovementAction
{
    public function handle(
        ProductVariant $variant,
        Location $location,
        StockMovementType $type,
        int $quantity,
        ?string $notes = null,
        ?Employee $performedBy = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?float $costPerUnit = null,
    ): StockMovement {
        if ($quantity === 0) {
            throw new \InvalidArgumentException('Số lượng phải lớn hơn 0');
        }

        $product = $variant->product;
        if ($product && ($product->is_dropship || $product->is_custom_made)) {
            throw new \InvalidArgumentException('Sản phẩm dropship hoặc custom-made không áp dụng quản lý tồn kho');
        }

        return DB::transaction(function () use ($variant, $location, $type, $quantity, $notes, $performedBy, $referenceType, $referenceId, $costPerUnit) {
            $inventory = Inventory::where('variant_id', $variant->id)
                ->where('location_id', $location->id)
                ->lockForUpdate()
                ->first();

            if ($type->isIncoming()) {
                if (! $inventory) {
                    $inventory = Inventory::create([
                        'variant_id' => $variant->id,
                        'location_id' => $location->id,
                        'quantity_on_hand' => 0,
                        'quantity_reserved' => 0,
                        'quantity_available' => 0,
                    ]);
                }

                $quantityBefore = $inventory->quantity_on_hand;
                $quantityAfter = $quantityBefore + $quantity;

                $inventory->quantity_on_hand = $quantityAfter;
                $inventory->quantity_available = $quantityAfter - $inventory->quantity_reserved;
                $inventory->save();

                if ($costPerUnit !== null && $costPerUnit > 0) {
                    $this->createValuationBatch($inventory, $quantity, $costPerUnit);
                }
            } else {
                if (! $inventory) {
                    throw new \RuntimeException(
                        "Không tìm thấy tồn kho cho variant {$variant->id} tại location {$location->id}"
                    );
                }

                $quantityBefore = $inventory->quantity_on_hand;
                $availableAfter = $inventory->quantity_available - $quantity;

                if ($availableAfter < 0) {
                    throw new \RuntimeException(
                        "Không đủ hàng tồn kho. Hiện tại: {$inventory->quantity_available}, Yêu cầu: {$quantity}"
                    );
                }

                $quantityAfter = $quantityBefore - $quantity;

                $inventory->quantity_on_hand = $quantityAfter;
                $inventory->quantity_available = $quantityAfter - $inventory->quantity_reserved;
                $inventory->save();

                $this->consumeValuationBatches($inventory, $quantity);
            }

            return StockMovement::create([
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'notes' => $notes,
                'performed_by' => $performedBy?->id,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }

    protected function createValuationBatch(Inventory $inventory, int $quantity, float $costPerUnit): StockValuation
    {
        return StockValuation::create([
            'variant_id' => $inventory->variant_id,
            'location_id' => $inventory->location_id,
            'batch_cost' => $costPerUnit,
            'quantity_remaining' => $quantity,
            'received_at' => now(),
        ]);
    }

    protected function consumeValuationBatches(Inventory $inventory, int $quantity): void
    {
        $remaining = $quantity;

        while ($remaining > 0) {
            $batch = StockValuation::where('variant_id', $inventory->variant_id)
                ->where('location_id', $inventory->location_id)
                ->where('quantity_remaining', '>', 0)
                ->orderBy('received_at', 'asc')
                ->lockForUpdate()
                ->first();

            if (! $batch) {
                break;
            }

            $consumed = $batch->consume($remaining);
            $remaining -= $consumed;
        }
    }
}
