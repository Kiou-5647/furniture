<?php

namespace App\Actions\Public;

use App\Data\Public\CartItemData;
use App\Models\Public\Cart;
use App\Models\Public\CartItem;

class AddToCartAction
{
    public function execute(Cart $cart, CartItemData $itemData): CartItem
    {
        $purchasableClass = $itemData->purchasable_type;
        $purchasable = $purchasableClass::findOrFail($itemData->purchasable_id);

        // Check for existing item with same variant/bundle AND same bundle configuration
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
            'configuration' => $itemData->configuration,
        ]);
    }
}
