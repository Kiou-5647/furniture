<?php

namespace App\Observers;

use App\Models\Product\Product;
use App\Models\Product\ProductCard;

class ProductObserver
{

    public function updated(Product $product): void
    {
        // Triggered when the product's global config changes
        if ($product->wasChanged('option_groups')) {
            // We need to re-assign every variant to the correct card
            // because the definition of a "Card" (non-swatches) has changed.
            $product->variants()->each(function ($variant) {
                // We manually trigger the observer logic by calling a helper
                // or simply updating the variant to fire the ProductVariantObserver::saving
                $variant->save();
            });

            // Optional: Clean up cards that no longer have variants
            ProductCard::where('product_id', $product->id)
                ->whereDoesntHave('variants')
                ->delete();
        }
    }
    public function deleting(Product $product): void
    {
        $product->variants()->delete();
    }

    public function restoring(Product $product): void
    {
        $product->variants()->restore();
    }

    public function forceDeleting(Product $product): void
    {
        $product->variants()->forceDelete();
    }
}
