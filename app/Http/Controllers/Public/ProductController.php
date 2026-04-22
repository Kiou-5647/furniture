<?php

namespace App\Http\Controllers\Public;

use App\Data\Public\ProductCardFilterData;
use App\Http\Resources\Public\Product\ProductPageResource;
use App\Models\Product\ProductVariant;
use App\Services\Public\StorefrontService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController
{
    public function __construct(protected StorefrontService $storefrontService) {}

    public function index(Request $request)
    {
        // We use the DTO to handle the filtering from the URL
        $filter = ProductCardFilterData::fromRequest($request);

        $cards = $this->storefrontService->getProductCards($filter);

        return Inertia::render('public/product/Index', [
            'cards' => $cards,
            'filters' => [
                'type' => $request->query('type'),
                'category' => $request->query('category'),
            ]
        ]);
    }

    /**
     * Display the specified product variant.
     */
    public function show(string $sku): Response
    {
        $variant = ProductVariant::where('sku', $sku)->firstOrFail();
        $product = $variant->product->load(['variants', 'category', 'collection']);
        request()->attributes->add(['activeVariant' => $variant]);

        return Inertia::render('public/product/Show', [
            'product_page' => new ProductPageResource($product),
        ]);
    }
}
