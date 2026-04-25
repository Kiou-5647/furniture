<?php

namespace App\Http\Controllers\Public;

use App\Data\Public\ProductCardFilterData;
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
                        new ProductCardFilterData(type: 'new', limit: 20)
                    ),
                    'moreUrl' => route('products.index', ['type' => 'new']),
                ],
                'topSellers' => [
                    'title' => 'Bán chạy nhất',
                    'cards' => $this->storefrontService->getPurchasables(
                        new ProductCardFilterData(type: 'top_seller', limit: 20)
                    ),
                    'moreUrl' => route('products.index', ['type' => 'top_seller']),
                ],
                'allProducts' => [
                    'title' => 'Tất cả sản phẩm',
                    'cards' => $this->storefrontService->getPurchasables(
                        new ProductCardFilterData(type: 'all', limit: 20)
                    ),
                    'moreUrl' => route('products.index'),
                ],
            ]
        ]);
    }
}
