<?php

namespace App\Services\Public;

use App\Data\Product\BundleFilterData;
use App\Data\Public\CartTotalsData;
use App\Models\Auth\User;
use App\Models\Public\Cart;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

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
        $cart->load('items.purchasable');

        $lineItems = [];
        $actualTotal = 0;       // The final amount the user actually pays
        $originalSum = 0;       // The absolute base price of every single item/variant
        $itemCount = 0;

        foreach ($cart->items as $item) {
            $quantity = $item->quantity;
            $effectivePrice = $item->getEffectivePrice();
            $itemActualTotal = $effectivePrice * $quantity;

            $actualTotal += $itemActualTotal;
            $itemCount += $quantity;

            // Calculate the "Original Price" for this line item
            $itemOriginalPrice = 0.0;

            if ($item->purchasable instanceof \App\Models\Product\ProductVariant) {
                // For simple items: base price * qty
                $itemOriginalPrice = (float) $item->purchasable->price * $quantity;
            } elseif ($item->purchasable instanceof \App\Models\Product\Bundle) {
                // For bundles: SUM of (base price of each component * component qty) * bundle qty
                $bundleBaseSum = 0.0;
                foreach ($item->purchasable->contents as $content) {
                    $variantId = $item->configuration[$content->id] ?? null;
                    $variant = $variantId ? \App\Models\Product\ProductVariant::find($variantId) : null;

                    // Use base price, NOT effective price, to find the absolute original value
                    $componentBasePrice = $variant ? (float) $variant->price : 0;
                    $bundleBaseSum += $componentBasePrice * $content->quantity;
                }
                $itemOriginalPrice = $bundleBaseSum * $quantity;
            }

            $originalSum += $itemOriginalPrice;

            $lineItems[] = [
                'id' => $item->id,
                'name' => $item->purchasable?->name ?? 'Unknown',
                'quantity' => $quantity,
                'unit_price' => $effectivePrice,
                'subtotal' => $itemActualTotal,
            ];
        }

        // Discount = (Sum of all base prices) - (Sum of all actual prices)
        // This automatically captures both Variant sales AND Bundle discounts
        $totalDiscount = max(0, $originalSum - $actualTotal);

        return new CartTotalsData(
            item_count: $itemCount,
            subtotal: $originalSum,     // Tạm tính = Absolute original value
            discount: $totalDiscount,   // Tiết kiệm = Total savings
            total: $actualTotal,        // Tổng cộng = Final price to pay
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

    public function merge(string $sessionId, User $user): void
    {
        $guestCart = Cart::query()
            ->where('user_id', null)
            ->where('session_id', $sessionId)
            ->first();

        if (!$guestCart) return;

        $userCart = $this->getOrCreateForUser($user);

        // Load all user cart items into a collection once to avoid N+1 queries
        $userItems = $userCart->items;

        foreach ($guestCart->items as $guestItem) {
            // Find matching item using PHP comparison (handles JSON order perfectly)
            $existingItem = $userItems->first(function ($item) use ($guestItem) {
                return $item->purchasable_id === $guestItem->purchasable_id &&
                    $item->purchasable_type === $guestItem->purchasable_type &&
                    $item->configuration === $guestItem->configuration;
            });

            if ($existingItem) {
                $existingItem->increment('quantity', $guestItem->quantity);
                $guestItem->delete();
            } else {
                $guestItem->update([
                    'cart_id' => $userCart->id,
                ]);
            }
        }
    }
}
