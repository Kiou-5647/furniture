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

        // Fetch products from same collection
        $collectionProducts = [];
        if ($product->collection) {
            $collectionFilter = new ProductCardFilterData(
                limit: 8,
                filters: ['bo-suu-tap' => [$product->collection->slug]],
            );
            $collectionProducts = $this->storefrontService->getProductCards($collectionFilter)->items();
        }

        // Fetch similar products (Heuristic: Same category, or same product type if limited)
        $similarProducts = [];
        if ($product->category) {
            // 1. Primary: Same category
            $similarFilter = new ProductCardFilterData(
                limit: 20,
                filters: ['danh-muc' => [$product->category->slug]],
            );
            
            $similarCards = collect($this->storefrontService->getProductCards($similarFilter)->items())
                ->filter(fn($card) => $card['id'] !== $product->product_card_id);

            // 2. Fallback/Expansion: Same product type (if we need more)
            if ($similarCards->count() < 8) {
                $typeFilter = new ProductCardFilterData(
                    limit: 20,
                    filters: ['loai' => [$product->category->product_type->value]],
                );
                
                $typeCards = collect($this->storefrontService->getProductCards($typeFilter)->items())
                    ->filter(fn($card) => $card['id'] !== $product->product_card_id && !in_array($card['id'], $similarCards->pluck('id')->toArray()));
                
                $similarCards = $similarCards->concat($typeCards);
            }

            $similarProducts = $similarCards->take(12)->values()->toArray();
        }

        request()->attributes->add([
            'collection_products' => $collectionProducts,
            'similar_products' => $similarProducts,
        ]);

        return Inertia::render('public/product/Show', [
            'product_page' => new ProductPageResource($product),
        ]);
    }
}
