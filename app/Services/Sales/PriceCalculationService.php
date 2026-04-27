<?php

namespace App\Services\Sales;

use App\Models\Product\ProductVariant;
use App\Models\Sales\Discount;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PriceCalculationService
{
    public function calculateEffectivePrice(ProductVariant $variant): float
    {
        return $this->calculatePriceDetails($variant)['effective_price'];
    }

    public function calculatePriceDetails(ProductVariant $variant): array
    {
        $basePrice = (float) $variant->price;
        $applicableDiscounts = $this->getApplicableDiscounts($variant);

        if ($applicableDiscounts->isEmpty()) {
            return [
                'effective_price' => $basePrice,
                'discount' => null,
            ];
        }

        $bestOption = $applicableDiscounts->map(function (Discount $discount) use ($basePrice) {
            return [
                'discount' => $discount,
                'final_price' => $this->applyDiscount($basePrice, $discount),
            ];
        })->sortBy('final_price')->first();

        $bestDiscount = $bestOption['discount'];

        return [
            'effective_price' => $bestOption['final_price'],
            'discount' => [
                'name' => $bestDiscount->name,
                'type' => $bestDiscount->type,
                'value' => $bestDiscount->value,
            ],
        ];
    }

    private function getApplicableDiscounts(ProductVariant $variant): Collection
    {
        $product = $variant->product;
        $now = Carbon::now();
        $discounts = collect();

        // Universal
        $discounts = $discounts->merge(Discount::whereNull('discountable_id')->active($now)->get());

        if ($product->category) {
            $discounts = $discounts->merge($product->category->discounts()->active($now)->get());
        }
        if ($product->collection) {
            $discounts = $discounts->merge($product->collection->discounts()->active($now)->get());
        }
        if ($product->vendor) {
            $discounts = $discounts->merge($product->vendor->discounts()->active($now)->get());
        }

        return $discounts;
    }

    private function applyDiscount(float $price, Discount $discount): float
    {
        return match ($discount->type) {
            'percentage' => max(0, $price * (1 - (float) $discount->value / 100)),
            'fixed_amount' => max(0, $price - (float) $discount->value),
            default => $price,
        };
    }
}
