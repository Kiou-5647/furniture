<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use App\Models\Product\Bundle;
use App\Models\Product\ProductVariant;
use App\Services\Analytics\ViewTrackingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            $user = Auth::user();

            // GUARD: Only allow Guests (no user) or Customers.
            if ($user && $user->type !== UserType::Customer) {
                return $response;
            }

            $targetId = null;
            $targetClass = null;

            $sku = $request->route('sku');
            if ($sku) {
                $targetId = ProductVariant::where('sku', $sku)->value('id');
                $targetClass = ProductVariant::class;
            }

            if ($targetId && $this->shouldTrackView($request, $user, $targetId)) {
                $this->viewTracker->incrementView($targetId, $targetClass);
            }
        }

        return $response;
    }

    protected function shouldTrackView(Request $request, $user, $targetId): bool
    {
        // Authenticated customers use their ID, guests use their session ID
        $viewerId = $user ? "user_{$user->id}" : "guest_{$request->getSession()->getId()}";

        $cacheKey = "product_view_{$viewerId}_{$targetId}";

        if (Cache::has($cacheKey)) {
            return false;
        }

        // Throttle changed to 3 hours for more dynamic tracking
        Cache::put($cacheKey, true, now()->addHours(3));

        return true;
    }
}
