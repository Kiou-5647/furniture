<?php

namespace App\Observers;

use App\Models\Product\Product;

class ProductObserver
{
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
