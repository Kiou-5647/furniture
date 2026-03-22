<?php

namespace App\Http\Controllers\Employee;

use App\Services\Employee\EmployeeDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeDashboardController
{
    public function __construct(
        public EmployeeDashboardService $service
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render(
            'employee/Dashboard',
            $this->service->getData($request->user())
        );
    }
}
