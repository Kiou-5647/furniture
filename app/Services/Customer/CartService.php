<?php

namespace App\Services\Customer;

use App\Data\Customer\CartItemData;
use App\Data\Customer\CartTotalsData;
use App\Data\Product\BundleFilterData;
use App\Models\Auth\User;
use App\Models\Customer\Cart;
use App\Models\Customer\CartItem;
use App\Models\Product\Bundle;
use App\Models\Product\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class CartService
{
    public function getOrCreateForUser(User $user): Cart
    {
        return Cart::getOrCreateForUser($user);
    }

    public function getCartWithItems(Cart $cart): Cart
    {
        return $cart->load('items.purchasable');
    }

    public function calculateTotals(Cart $cart): CartTotalsData
    {
        $cart->load('items');

        $lineItems = [];
        $subtotal = 0;
        $itemCount = 0;

        foreach ($cart->items as $item) {
            $lineSubtotal = (float) $item->unit_price * $item->quantity;
            $subtotal += $lineSubtotal;
            $itemCount += $item->quantity;

            $lineItems[] = [
                'id' => $item->id,
                'name' => $item->purchasable?->name ?? 'Unknown',
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => $lineSubtotal,
            ];
        }

        // Bundles already calculate their discounted price, so subtotal = total
        $discount = 0;

        return new CartTotalsData(
            item_count: $itemCount,
            subtotal: $subtotal,
            discount: $discount,
            total: $subtotal,
            line_items: $lineItems,
        );
    }

    public function validateCart(Cart $cart): array
    {
        $cart->load('items');
        $errors = [];

        foreach ($cart->items as $item) {
            if (! $item->validateAvailability()) {
                $name = $item->purchasable?->name ?? 'Unknown item';
                $errors[] = "Sản phẩm '{$name}' không còn khả dụng.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function getOpenCarts(BundleFilterData $filter): LengthAwarePaginator
    {
        return Cart::query()
            ->open()
            ->with(['user', 'items'])
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getAbandonedCarts(BundleFilterData $filter): LengthAwarePaginator
    {
        return Cart::query()
            ->abandoned()
            ->with(['user', 'items'])
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function addItem(Cart $cart, CartItemData $itemData): CartItem
    {
        $purchasableClass = $itemData->purchasable_type;
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
