<?php

namespace App\Http\Middleware;

use App\Services\VendorVerificationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorVerified
{
    public function __construct(
        public VendorVerificationService $service
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isVendor()) {
            return $next($request);
        }
        $excludedPaths = [
            '/nha-cung-cap/cho-xac-minh',
            '/cai-dat',
        ];
        $currentPath = $request->path();

        foreach ($excludedPaths as $path) {
            if (str_starts_with($currentPath, $path)) {
                return $next($request);
            }
        }
        if (! $this->service->isVendorVerified($user)) {
            return redirect()->route('vendor.pending-verification');
        }

        return $next($request);
    }
}
