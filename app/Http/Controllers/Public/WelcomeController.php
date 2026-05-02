<?php

namespace App\Http\Controllers\Public;

use App\Data\Public\ProductCardFilterData;
use App\Enums\ProductSortType;
use App\Services\Public\ShopMenuService;
use App\Services\Public\StorefrontService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class WelcomeController
{
    public function __construct(
        private ShopMenuService $shopMenuService,
        private StorefrontService $storefrontService
    ) {}

    public function __invoke(Request $request): Response
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'rooms' => $this->shopMenuService->getRooms(),
            'sections' => [
                'newArrivals' => [
                    'title' => 'Sản phẩm & Combo mới',
                    'cards' => $this->storefrontService->getPurchasables(
                        new ProductCardFilterData(type: ProductSortType::NEWEST, limit: 20)
                    ),
                    'moreUrl' => route('products.index', ['type' => ProductSortType::NEWEST->value]),
                ],
                'topSellers' => [
                    'title' => 'Bán chạy nhất',
                    'cards' => $this->storefrontService->getPurchasables(
                        new ProductCardFilterData(type: ProductSortType::POPULARITY, limit: 20)
                    ),
                    'moreUrl' => route('products.index', ['type' => ProductSortType::POPULARITY->value]),
                ],
                'allProducts' => [
                    'title' => 'Tất cả sản phẩm',
                    'cards' => $this->storefrontService->getPurchasables(
                        new ProductCardFilterData(type: null, limit: 20)
                    ),
                    'moreUrl' => route('products.index'),
                ],
            ]
        ]);
    }
}
