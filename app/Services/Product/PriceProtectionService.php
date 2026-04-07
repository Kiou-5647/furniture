<?php

namespace App\Services\Product;

use App\Models\Inventory\Inventory;
use App\Models\Product\ProductVariant;

class PriceProtectionService
{
    public function __construct(
        private PriceCalculationService $priceCalculation,
    ) {}

    public function recalculateWithProtection(ProductVariant $variant, ?string $locationId = null): PriceChangeResult
    {
        $oldPrice = (float) $variant->price;
        $newPrice = $this->priceCalculation->calculatePrice($variant, $locationId);
        $floorPrice = $this->calculateFloorPrice($variant);

        $finalPrice = max($newPrice, $floorPrice);

        $priceWouldDecrease = $newPrice < $oldPrice;
        $belowFloor = $newPrice < $floorPrice;

        if ($belowFloor) {
            $newPrice = $floorPrice;
            $finalPrice = $floorPrice;
        }

        return new PriceChangeResult(
            oldPrice: $oldPrice,
            newPrice: $newPrice,
            finalPrice: $finalPrice,
            floorPrice: $floorPrice,
            priceWouldDecrease: $priceWouldDecrease,
            belowFloor: $belowFloor,
            requiresConfirmation: $priceWouldDecrease,
        );
    }

    public function calculateFloorPrice(ProductVariant $variant): float
    {
        $margin = (float) ($variant->profit_margin_value ?? 0);

        if ($margin <= 0) {
            return 0.0;
        }

        $cost = $this->getAverageCost($variant);

        if ($cost <= 0) {
            return 0.0;
        }

        if ($variant->profit_margin_unit === 'percentage') {
            return $cost * (1 + $margin / 100);
        }

        return $cost + $margin;
    }

    protected function getAverageCost(ProductVariant $variant): ?float
    {
        $totalQty = $variant->inventories()->sum('quantity');

        if ($totalQty === 0) {
            return null;
        }

        $totalValue = $variant->inventories()
            ->selectRaw('SUM(cost_per_unit * quantity) as total')
            ->value('total');

        return $totalValue > 0 ? (float) $totalValue / $totalQty : null;
    }

    public function getPricePreview(ProductVariant $variant, array $stockChanges, ?string $locationId = null): array
    {
        $currentCost = $this->getAverageCost($variant);
        $currentPrice = (float) $variant->price;
        $floorPrice = $this->calculateFloorPrice($variant);

        $simulatedCost = $this->simulateWAC($variant, $stockChanges, $locationId);
        $simulatedMargin = (float) ($variant->profit_margin_value ?? 0);

        if ($simulatedCost === null || $simulatedMargin <= 0) {
            return [
                'current_cost' => $currentCost,
                'simulated_cost' => $simulatedCost,
                'current_price' => $currentPrice,
                'simulated_price' => $simulatedCost ?? 0,
                'floor_price' => $floorPrice,
                'price_would_decrease' => false,
                'requires_confirmation' => false,
            ];
        }

        $simulatedNewPrice = $simulatedMargin > 0 && $variant->profit_margin_unit === 'percentage'
            ? $simulatedCost * (1 + $simulatedMargin / 100)
            : $simulatedCost + $simulatedMargin;

        $simulatedFinalPrice = max($simulatedNewPrice, $floorPrice);

        return [
            'current_cost' => $currentCost,
            'simulated_cost' => $simulatedCost,
            'current_price' => $currentPrice,
            'simulated_price' => $simulatedNewPrice,
            'final_price' => $simulatedFinalPrice,
            'floor_price' => $floorPrice,
            'price_would_decrease' => $simulatedNewPrice < $currentPrice,
            'requires_confirmation' => $simulatedNewPrice < $currentPrice,
        ];
    }

    protected function simulateWAC(ProductVariant $variant, array $stockChanges, ?string $locationId): ?float
    {
        $query = Inventory::where('variant_id', $variant->id);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $currentInventories = $query->get();

        $totalValue = 0;
        $totalQty = 0;

        foreach ($currentInventories as $inv) {
            if ($inv->quantity > 0) {
                $totalValue += (float) $inv->cost_per_unit * $inv->quantity;
                $totalQty += $inv->quantity;
            }
        }

        foreach ($stockChanges as $change) {
            $changeLocationId = $change['location_id'] ?? null;
            $addQty = (int) ($change['add_quantity'] ?? 0);
            $addCost = (float) ($change['add_cost'] ?? 0);

            if ($addQty > 0 && $addCost > 0) {
                if ($locationId === null || $changeLocationId === $locationId) {
                    $totalValue += $addCost * $addQty;
                    $totalQty += $addQty;
                }
            }
        }

        if ($totalQty === 0) {
            return null;
        }

        return $totalValue / $totalQty;
    }
}

class PriceChangeResult
{
    public function __construct(
        public float $oldPrice,
        public float $newPrice,
        public float $finalPrice,
        public float $floorPrice,
        public bool $priceWouldDecrease,
        public bool $belowFloor,
        public bool $requiresConfirmation,
    ) {}

    public function shouldUpdate(): bool
    {
        return $this->finalPrice > 0 && $this->finalPrice !== $this->oldPrice;
    }

    public function toArray(): array
    {
        return [
            'old_price' => $this->oldPrice,
            'new_price' => $this->newPrice,
            'final_price' => $this->finalPrice,
            'floor_price' => $this->floorPrice,
            'price_would_decrease' => $this->priceWouldDecrease,
            'below_floor' => $this->belowFloor,
            'requires_confirmation' => $this->requiresConfirmation,
        ];
    }
}
