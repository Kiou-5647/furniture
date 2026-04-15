<?php

namespace App\Http\Controllers\Public;

use App\Http\Resources\Public\Product\ProductResource;
use App\Http\Resources\Public\Product\ProductVariantResource;
use App\Models\Product\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController
{
    /**
     * Display the specified product variant.
     */
    public function show(string $sku): Response
    {
        $variant = ProductVariant::where('sku', $sku)->firstOrFail();
        $product = $variant->product->load(['variants', 'category', 'collection']);


        return Inertia::render('public/product/Show', [
            'product' => new ProductResource($product),
            'activeVariant' => new ProductVariantResource($variant),
        ]);
    }
}
