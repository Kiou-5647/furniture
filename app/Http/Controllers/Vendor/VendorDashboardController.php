<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\VendorDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorDashboardController extends Controller
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
