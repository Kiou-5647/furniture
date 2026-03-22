<?php

namespace App\Http\Controllers\Vendor;

use App\Services\Vendor\VendorDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorDashboardController
{
    public function __construct(
        public VendorDashboardService $service
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render(
            'vendor/Dashboard',
            $this->service->getData($request->user())
        );
    }
}
