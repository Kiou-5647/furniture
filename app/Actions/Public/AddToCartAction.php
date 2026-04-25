<?php

namespace App\Actions\Public;

use App\Data\Public\CartItemData;
use App\Models\Public\Cart;
use App\Models\Public\CartItem;
use App\Models\Product\Bundle;
use App\Models\Product\ProductVariant;

class AddToCartAction
{
    public function execute(Cart $cart, CartItemData $itemData): CartItem
    {
        $purchasableClass = $itemData->purchasable_type;
        $purchasable = $purchasableClass::findOrFail($itemData->purchasable_id);

        // Price is derived from the Variant or the Bundle's calculation logic
        $price = match (true) {
            $purchasable instanceof ProductVariant => (float) ($purchasable->sale_price ?? $purchasable->price),
            $purchasable instanceof Bundle => (float) $purchasable->calculateBundlePrice($itemData->configuration),
            default => 0,
        };

        // Check for existing item with same variant/bundle AND same bundle configuration
        $existingItem = $cart->items()
            ->where('purchasable_type', $itemData->purchasable_type)
            ->where('purchasable_id', $itemData->purchasable_id)
            ->where('configuration', json_encode($itemData->configuration))
            ->first();

        if ($existingItem) {
            if ($existingItem->unit_price != $price) {
                $existingItem->update(['unit_price' => $price]);
            }

            $existingItem->increment('quantity', $itemData->quantity);
            return $existingItem;
        }

        return $cart->items()->create([
            'purchasable_type' => $itemData->purchasable_type,
            'purchasable_id' => $itemData->purchasable_id,
            'quantity' => $itemData->quantity,
            'unit_price' => $price,
            'configuration' => $itemData->configuration,
        ]);
    }
}
