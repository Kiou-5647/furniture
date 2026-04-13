<?php

namespace App\Http\Controllers\Customer\Booking;

use App\Models\Booking\DesignService;
use Inertia\Inertia;
use Inertia\Response;

class DesignServiceController
{
    public function index(): Response
    {
        $services = DesignService::whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'base_price', 'deposit_percentage', 'estimated_hours', 'is_schedule_blocking']);

        return Inertia::render('customer/booking/Services', [
            'services' => $services,
        ]);
    }
}
