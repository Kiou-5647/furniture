<?php

namespace App\Services\Public;

use App\Data\Public\CartTotalsData;
use App\Data\Product\BundleFilterData;
use App\Models\Auth\User;
use App\Models\Public\Cart;
use Illuminate\Pagination\LengthAwarePaginator;

class CartService
{
    public function getOrCreateForUser(?User $user, ?string $sessionId = null): Cart
    {
        return Cart::getOrCreate($user, $sessionId);
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
            $price = $item->getEffectivePrice();
            $lineSubtotal = $price * $item->quantity;

            $subtotal += $lineSubtotal;
            $itemCount += $item->quantity;

            $lineItems[] = [
                'id' => $item->id,
                'name' => $item->purchasable?->name ?? 'Unknown',
                'quantity' => $item->quantity,
                'unit_price' => $price,
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
}
