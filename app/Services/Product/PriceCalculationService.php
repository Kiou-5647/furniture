<?php

namespace App\Services\Product;

use App\Models\Product\ProductVariant;

class PriceCalculationService
{
    public function calculatePrice(ProductVariant $variant, ?string $locationId = null): float
    {
        $cost = $this->getCostForPricing($variant, $locationId);
        if ($cost === null || $cost <= 0) {
            return 0.0;
        }

        $margin = (float) ($variant->profit_margin_value ?? 0);
        if ($margin <= 0) {
            return $cost;
        }

        if ($variant->profit_margin_unit === 'percentage') {
            return $cost * (1 + $margin / 100);
        }

        return $cost + $margin;
    }

    public function recalculatePrice(ProductVariant $variant): void
    {
        $price = $this->calculatePrice($variant);
        $variant->updateQuietly(['price' => $price]);
    }

    protected function getCostForPricing(ProductVariant $variant, ?string $locationId = null): ?float
    {
        $query = $variant->inventories()->where('quantity', '>', 0);

        if ($locationId) {
            $query->where('location_id', $locationId);
            $inventory = $query->first();

            return $inventory ? (float) $inventory->cost_per_unit : null;
        }

        $totalQty = $variant->inventories()->sum('quantity');
        if ($totalQty === 0) {
            return null;
        }

        $totalValue = $variant->inventories()
            ->selectRaw('SUM(cost_per_unit * quantity) as total')
            ->value('total');

        return $totalValue > 0 ? (float) $totalValue / $totalQty : null;
    }

    public function getWeightedAverageCost(ProductVariant $variant, ?string $locationId = null): ?float
    {
        return $this->getCostForPricing($variant, $locationId);
    }
}
