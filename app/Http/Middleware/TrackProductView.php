<?php

namespace App\Http\Middleware;

use App\Models\Product\ProductVariant;
use App\Models\Product\Bundle;
use App\Services\Analytics\ViewTrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackProductView
{
    public function __construct(
        protected ViewTrackingService $viewTracker
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 200) {
            $user = $request->user();

            if ($user && $user->user_type === 'customer') {
                // 1. Check if it's a Product Variant (via SKU)
                $sku = $request->route('sku');
                if ($sku) {
                    $variantId = ProductVariant::where('sku', $sku)->value('id');
                    if ($variantId) {
                        $this->viewTracker->incrementView($variantId, ProductVariant::class);
                        return $response;
                    }
                }

                // 2. Check if it's a Bundle (via Slug)
                $bundleSlug = $request->route('bundle_slug');
                if ($bundleSlug) {
                    $bundleId = Bundle::where('slug', $bundleSlug)->value('id');
                    if ($bundleId) {
                        $this->viewTracker->incrementView($bundleId, Bundle::class);
                    }
                }
            }
        }

        return $response;
    }
}
