<?php

namespace App\Actions\Inventory;

use App\Enums\StockMovementType;
use App\Models\Employee\Employee;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockMovement;
use App\Models\Product\ProductVariant;
use App\Services\Product\PriceProtectionService;
use Illuminate\Support\Facades\DB;

class RecordStockMovementAction
{
    public function __construct(
        private PriceProtectionService $priceProtection,
    ) {}

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
        bool $forceUpdatePrice = false,
    ): StockMovement {
        if ($quantity === 0) {
            throw new \InvalidArgumentException('Số lượng phải lớn hơn 0');
        }

        $product = $variant->product;
        if ($product && ($product->is_dropship || $product->is_custom_made)) {
            throw new \InvalidArgumentException('Sản phẩm dropship hoặc custom-made không áp dụng quản lý tồn kho');
        }

        return DB::transaction(function () use ($variant, $location, $type, $quantity, $notes, $performedBy, $referenceType, $referenceId, $costPerUnit, $forceUpdatePrice) {
            $inventory = Inventory::where('variant_id', $variant->id)
                ->where('location_id', $location->id)
                ->lockForUpdate()
                ->first();

            // Capture BEFORE any modifications
            $quantityBefore = $inventory?->quantity ?? 0;
            $costPerUnitBefore = $inventory?->cost_per_unit !== null && $inventory?->cost_per_unit > 0
                ? (float) $inventory->cost_per_unit
                : null;
            $costPerUnitAfter = null;

            if ($type->isIncoming()) {
                $result = $this->handleIncoming($inventory, $variant, $location, $quantity, $costPerUnit);
                $costPerUnitAfter = $result['cost'];
                $inventory = $result['inventory'];
            } elseif ($type === StockMovementType::Adjust) {
                $result = $this->handleAdjustment($inventory, $variant, $location, $quantity);
                $costPerUnitAfter = $result['cost'];
                $inventory = $result['inventory'];
            } else {
                $result = $this->handleOutgoing($inventory, $variant, $location, $quantity);
                $costPerUnitAfter = $result['cost'];
                $inventory = $result['inventory'];
            }

            $quantityAfter = $inventory->quantity;

            $movement = StockMovement::create([
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'cost_per_unit_before' => $costPerUnitBefore,
                'cost_per_unit' => $costPerUnitAfter,
                'notes' => $notes,
                'performed_by' => $performedBy?->id,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            $this->updatePrice($variant, $forceUpdatePrice);

            return $movement;
        });
    }

    protected function handleIncoming(?Inventory $inventory, ProductVariant $variant, Location $location, int $quantity, ?float $costPerUnit): array
    {
        $oldQty = 0;
        $oldCost = 0.0;
        $newCost = $costPerUnit ?? 0.0;

        if (! $inventory) {
            $inventory = Inventory::create([
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => 0,
                'cost_per_unit' => $newCost > 0 ? $newCost : 0,
            ]);
        } else {
            $oldQty = $inventory->quantity;
            $oldCost = (float) $inventory->cost_per_unit;
        }

        if ($oldQty > 0 && $newCost > 0) {
            $newAvgCost = ($oldCost * $oldQty + $newCost * $quantity) / ($oldQty + $quantity);
            $inventory->cost_per_unit = $newAvgCost;
        } elseif ($newCost > 0) {
            $inventory->cost_per_unit = $newCost;
        }

        $inventory->quantity += $quantity;
        $inventory->save();

        return [
            'cost' => (float) $inventory->cost_per_unit,
            'inventory' => $inventory,
        ];
    }

    protected function handleAdjustment(?Inventory $inventory, ProductVariant $variant, Location $location, int $quantity): array
    {
        if (! $inventory) {
            $inventory = Inventory::create([
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => 0,
            ]);
        }

        $inventory->quantity = max(0, $inventory->quantity - $quantity);
        $inventory->save();

        return [
            'cost' => (float) $inventory->cost_per_unit,
            'inventory' => $inventory,
        ];
    }

    protected function handleOutgoing(Inventory $inventory, ProductVariant $variant, Location $location, int $quantity): array
    {
        if ($inventory->quantity < $quantity) {
            throw new \RuntimeException(
                "Không đủ hàng tồn kho. Hiện tại: {$inventory->quantity}, Yêu cầu: {$quantity}"
            );
        }

        $inventory->quantity -= $quantity;
        $inventory->save();

        return [
            'cost' => (float) $inventory->cost_per_unit,
            'inventory' => $inventory,
        ];
    }

    protected function updatePrice(ProductVariant $variant, bool $forceUpdate = false): void
    {
        $priceResult = $this->priceProtection->recalculateWithProtection($variant);

        if (! $priceResult->requiresConfirmation) {
            $variant->updateQuietly(['price' => $priceResult->finalPrice]);

            return;
        }

        if ($forceUpdate) {
            $variant->updateQuietly(['price' => $priceResult->finalPrice]);

            return;
        }

        // Price would decrease and not forced - keep old price
        // This is handled by not calling updateQuietly
    }
}
