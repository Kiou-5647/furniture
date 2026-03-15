<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\EmployeeDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeDashboardController extends Controller
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
