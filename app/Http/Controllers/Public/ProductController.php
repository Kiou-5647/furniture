<?php

namespace App\Http\Controllers\Public;

use App\Data\Public\ProductCardFilterData;
use App\Http\Resources\Public\Product\ProductPageResource;
use App\Models\Product\ProductVariant;
use App\Services\Public\ShopMenuService;
use App\Services\Public\StorefrontService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController
{
    public function __construct(
        protected StorefrontService $storefrontService,
        protected ShopMenuService $shopMenuService
    ) {}

    public function index(Request $request)
    {
        $filter = ProductCardFilterData::fromRequest($request);

        $purchasables = $this->storefrontService->getPurchasables($filter);

        $filterSummary = $this->storefrontService->getFilterSummary($filter);

        return Inertia::render('public/product/Index', [
            'cards' => $purchasables,
            'filters' => $filter,
            'filterSummary' => $filterSummary,
            'totalItems' => $purchasables->total(),
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
