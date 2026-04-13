<?php

namespace App\Actions\Product;

use App\Models\Product\Product;

class SyncProductPriceRangeAction
{
    public function execute(string $productId): void
    {
        /** @var App\Models\Product\Product $product */
        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $product->updateQuietly([
            'min_price' => $product->variants()->min('price') ?? 0,
            'max_price' => $product->variants()->max('price') ?? 0,
        ]);
    }
}
