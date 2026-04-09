<?php

namespace App\Actions\Customer;

use App\Data\Customer\CartItemData;
use App\Models\Customer\Cart;
use App\Models\Customer\CartItem;
use App\Models\Product\Bundle;
use App\Models\Product\Product;

class AddToCartAction
{
    public function execute(Cart $cart, CartItemData $itemData): CartItem
    {
        $purchasableClass = $itemData->purchasable_type;

        /** @var Product|Bundle $purchasable */
        $purchasable = $purchasableClass::findOrFail($itemData->purchasable_id);

        $price = match (true) {
            $purchasable instanceof Product => $purchasable->min_price,
            $purchasable instanceof Bundle => $purchasable->calculateBundlePrice(),
            default => 0,
        };

        $existingItem = $cart->items()
            ->where('purchasable_type', $itemData->purchasable_type)
            ->where('purchasable_id', $itemData->purchasable_id)
            ->where('configuration', json_encode($itemData->configuration))
            ->first();

        if ($existingItem) {
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
