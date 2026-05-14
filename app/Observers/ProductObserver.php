<?php

namespace App\Observers;

use App\Models\Product\Product;
use App\Models\Product\ProductCard;

class ProductObserver
{

    public function updated(Product $product): void
    {
        if ($product->wasChanged('option_groups')) {
            $product->variants()->each(function ($variant) {
                $variant->save();
            });

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
