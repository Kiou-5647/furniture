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
        $user = $request->user();
        return Inertia::render(
            'employee/Dashboard',
            [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray(),
                    'permissions' => $user->getPermissionNames()->toArray(),
                ],
                'tables' => [
                    'recent_orders' => $this->service->getRecentOrders(),
                    'recent_bookings' => $this->service->getRecentBookings(),
                    'low_stock' => $this->service->getLowStockItems(),
                ]
            ]
        );
    }

    public function getSummary(Request $request)
    {
        return response()->json(
            $this->service->getSummary(
                $request->user(),
                $request->query('period', 'today')
            )
        );
    }

    public function getOrdersTrend(Request $request)
    {
        return response()->json(
            $this->service->getOrdersTrend(
                $request->user(),
                $request->query('period', 'month')
            )
        );
    }

    public function getFinancialAnalysis(Request $request)
    {
        return response()->json(
            $this->service->getFinancialAnalysis(
                $request->user(),
                $request->query('period', 'month')
            )
        );
    }
}
