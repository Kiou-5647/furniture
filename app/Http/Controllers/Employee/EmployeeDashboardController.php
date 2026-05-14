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

        $dashboardData = $this->service->getData($user);

        return Inertia::render(
            'employee/Dashboard',
            [
                'user' => $dashboardData['user'],
                'employee' => $dashboardData['employee'],
                'tables' => [
                    'recent_orders' => $dashboardData['tables']['recent_orders'],
                    'recent_bookings' => $dashboardData['tables']['recent_bookings'],
                    'low_stock' => $dashboardData['tables']['low_stock'],
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

    public function getOrderStatusDistribution(Request $request)
    {
        return response()->json(
            $this->service->getOrderStatusDistribution(
                $request->user(),
                $request->query('period', 'month')
            )
        );
    }

    public function getBookingStatusDistribution(Request $request)
    {
        return response()->json(
            $this->service->getBookingStatusDistribution(
                $request->user(),
                $request->query('period', 'month')
            )
        );
    }

    public function getRefundStatusDistribution(Request $request)
    {
        return response()->json(
            $this->service->getRefundStatusDistribution(
                $request->user(),
                $request->query('period', 'month')
            )
        );
    }
}
