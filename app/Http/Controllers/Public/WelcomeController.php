<?php

namespace App\Http\Controllers\Public;

use App\Services\Product\ShopMenuService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class WelcomeController
{
    public function __construct(
        private ShopMenuService $shopMenuService,
    ) {}

    public function __invoke(Request $request): Response
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'rooms' => $this->shopMenuService->getRooms(),
        ]);
    }
}
