<?php

namespace App\Services\Employee;

use App\Enums\BookingStatus;
use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Enums\ShipmentStatus;
use App\Models\Auth\User;
use App\Models\Booking\Booking;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Product\Bundle;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Models\Sales\PaymentAllocation;
use App\Models\Sales\Refund;
use App\Services\Booking\BookingService;
use App\Services\Inventory\LocationService;
use App\Services\Sales\OrderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EmployeeDashboardService
{
    public function __construct(
        protected OrderService $orderService,
        protected BookingService $bookingService,
        protected LocationService $locationService,
    ) {}

    public function getData(User $user, string $period = 'year'): array
    {
        // 1. Thông tin định danh: Trả về tên, email và các quyền hạn (roles, permissions)
        // để Frontend quyết định hiển thị những nút bấm/tính năng nào.
        return [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'can' => [
                    'order'     => Gate::allows('viewAny', Order::class),
                    'booking'   => Gate::allows('viewAny', Booking::class),
                    'inventory' => Gate::allows('viewAny', Location::class),
                    'refund'    => Gate::allows('viewAny', Refund::class),
                ],
            ],
            'employee' => $user->employee,
            'period' => $period,
            'summary' => $this->getSummary($user, $period),
            'charts' => [
                'orders_trend' => $this->getOrdersTrend($user, $period),
                'financial_analysis' => $this->getFinancialAnalysis($user, $period),
                'order_distribution' => $this->getOrderStatusDistribution($user),
                'booking_distribution' => $this->getBookingStatusDistribution($user),
                'refund_distribution' => $this->getRefundStatusDistribution($user),
            ],
            'tables' => [
                'recent_orders' => $this->getRecentOrders($user),
                'recent_bookings' => $this->getRecentBookings($user),
                'low_stock' => $this->getLowStockItems($user),
            ],
        ];
    }

    public function getSummary(User $user, string $period = 'month'): array
    {
        $start = match ($period) {
            'today' => Carbon::now()->startOfDay(),
            'week' => Carbon::now()->subDays(7)->startOfDay(),
            'month' => Carbon::now()->startOfMonth(),
            'quarter' => Carbon::now()->subMonths(3)->startOfMonth(),
            default => Carbon::now()->subYear()->startOfMonth(),
        };

        $end = Carbon::now();
        $prevStart = $start->copy()->subDays($start->diffInDays($end));

        $customersCount = User::count();

        $orderQuery = Order::query();
        $this->orderService->applyRoleFilter($orderQuery, $user);
        $allowedOrderIds = $orderQuery->pluck('id');

        $ordersCurrent = Order::whereIn('id', $allowedOrderIds)->whereBetween('created_at', [$start, $end])->count();
        $ordersPrev = Order::whereIn('id', $allowedOrderIds)->whereBetween('created_at', [$prevStart, $start])->count();

        $bookingQuery = Booking::query();
        $this->bookingService->applyRoleFilter($bookingQuery, $user);
        $allowedBookingIds = $bookingQuery->pluck('id');

        $bookingsCurrent = Booking::whereIn('id', $allowedBookingIds)->whereBetween('created_at', [$start, $end])->count();
        $bookingsPrev = Booking::whereIn('id', $allowedBookingIds)->whereBetween('created_at', [$prevStart, $start])->count();

        $invoiceIds = Invoice::where(function ($q) use ($allowedOrderIds) {
            $q->where('invoiceable_type', Order::class)->whereIn('invoiceable_id', $allowedOrderIds);
        })->orWhere(function ($q) use ($allowedBookingIds) {
            $q->where('invoiceable_type', Booking::class)->whereIn('invoiceable_id', $allowedBookingIds);
        })->pluck('id');

        $revenueCurrent = (float) PaymentAllocation::whereIn('invoice_id', $invoiceIds)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount_applied');

        $revenuePrev = (float) PaymentAllocation::whereIn('invoice_id', $invoiceIds)
            ->whereBetween('created_at', [$prevStart, $start])
            ->sum('amount_applied');

        $profitCurrent = $this->calculatePeriodProfit($start, $end, $user);
        $profitPrev = $this->calculatePeriodProfit($prevStart, $start, $user);

        $refundsCurrent = (float) Refund::where(function ($q) use ($allowedOrderIds, $allowedBookingIds) {
            $q->whereIn('order_id', $allowedOrderIds)->orWhereIn('booking_id', $allowedBookingIds);
        })
            ->where('status', RefundStatus::Completed)
            ->whereBetween('processed_at', [$start, $end])
            ->sum('amount');

        $refundsPrev = (float) Refund::where(function ($q) use ($allowedOrderIds, $allowedBookingIds) {
            $q->whereIn('order_id', $allowedOrderIds)->orWhereIn('booking_id', $allowedBookingIds);
        })
            ->where('status', RefundStatus::Completed)
            ->whereBetween('processed_at', [$prevStart, $start])
            ->sum('amount');

        return [
            'customers' => ['value' => $customersCount, 'trend' => 0],
            'orders' => [
                'value' => $ordersCurrent,
                'trend' => $this->calculateTrend($ordersPrev, $ordersCurrent),
            ],
            'bookings' => [
                'value' => $bookingsCurrent,
                'trend' => $this->calculateTrend($bookingsPrev, $bookingsCurrent),
            ],
            'revenue' => [
                'value' => $revenueCurrent,
                'trend' => $this->calculateTrend($revenuePrev, $revenueCurrent),
            ],
            'profit' => [
                'value' => $profitCurrent,
                'trend' => $this->calculateTrend($profitPrev, $profitCurrent),
            ],
            'refunds' => [
                'value' => $refundsCurrent,
                'trend' => $this->calculateTrend($refundsPrev, $refundsCurrent),
            ],
        ];
    }

    protected function calculateTrend($old, $new): float
    {
        if ($old == 0) return $new > 0 ? 100 : 0;
        return (($new - $old) / $old) * 100;
    }

    public function calculatePeriodProfit(\DateTimeInterface $start, \DateTimeInterface $end, User $user): float
    {
        $orderQuery = Order::query();
        $this->orderService->applyRoleFilter($orderQuery, $user);
        $allowedOrderIds = $orderQuery->pluck('id');

        $bookingQuery = Booking::query();
        $this->bookingService->applyRoleFilter($bookingQuery, $user);
        $allowedBookingIds = $bookingQuery->pluck('id');

        // 1. Revenue (Cash Basis)
        $invoiceIds = Invoice::where(function ($q) use ($allowedOrderIds) {
            $q->where('invoiceable_type', Order::class)->whereIn('invoiceable_id', $allowedOrderIds);
        })->orWhere(function ($q) use ($allowedBookingIds) {
            $q->where('invoiceable_type', Booking::class)->whereIn('invoiceable_id', $allowedBookingIds);
        })->pluck('id');

        $revenue = (float) PaymentAllocation::whereIn('invoice_id', $invoiceIds)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount_applied');

        // 2. COGS (Cash Basis - Đối ứng với tiền thu về)
        // Lấy các Invoice có tiền thu về trong kỳ
        $paidInvoiceIds = PaymentAllocation::whereBetween('created_at', [$start, $end])
            ->whereIn('invoice_id', $invoiceIds)
            ->pluck('invoice_id')
            ->unique();

        // Lấy các Order liên kết với các Invoice đó
        $ordersWithActualPayment = Order::whereHas('invoices', function ($q) use ($paidInvoiceIds) {
            $q->whereIn('id', $paidInvoiceIds);
        })->get();

        $cogs = 0;
        foreach ($ordersWithActualPayment as $order) {
            foreach ($order->items as $item) {
                if ($item->purchasable_type === Bundle::class && !empty($item->configuration)) {
                    foreach ($item->configuration as $config) {
                        $cogs += ($config['cost'] ?? 0) * $item->quantity;
                    }
                } else {
                    $cogs += ($item->cost_per_unit ?? 0) * $item->quantity;
                }
            }
        }

        // 3. Refunds (Cash Basis - processed_at)
        $refunds = (float) Refund::where(function ($q) use ($allowedOrderIds, $allowedBookingIds) {
            $q->whereIn('order_id', $allowedOrderIds)->orWhereIn('booking_id', $allowedBookingIds);
        })
            ->where('status', RefundStatus::Completed)
            ->whereBetween('processed_at', [$start, $end])
            ->sum('amount');

        // 4. Recovered Cost (Cash Basis)
        $recoveredCost = 0;
        $shipmentItems = ShipmentItem::whereHas('orderItem.order', function ($query) use ($allowedOrderIds) {
            $query->whereIn('id', $allowedOrderIds);
        })
            ->where('status', ShipmentStatus::Returned)
            ->whereBetween('updated_at', [$start, $end]) // Lấy ngày hàng quay về kho
            ->get();

        foreach ($shipmentItems as $sItem) {
            $orderItem = $sItem->orderItem;
            if (!$orderItem) continue;
            if ($orderItem->purchasable_type === Bundle::class && !empty($orderItem->configuration)) {
                foreach ($orderItem->configuration as $config) {
                    if (($config['variant_id'] ?? null) === $sItem->variant_id) {
                        $recoveredCost += ($config['cost'] ?? 0) * $sItem->quantity_shipped;
                    }
                }
            } else {
                $recoveredCost += ($orderItem->cost_per_unit ?? 0) * $sItem->quantity_shipped;
            }
        }

        return ($revenue + $recoveredCost) - ($cogs + $refunds);
    }

    public function getOrdersTrend(User $user, string $period = 'month'): array
    {
        $startDate = match ($period) {
            'today' => Carbon::now()->startOfDay(),
            'week' => Carbon::now()->subDays(7),
            'month' => Carbon::now()->subDays(30),
            'quarter' => Carbon::now()->subMonths(3)->startOfMonth(),
            default => Carbon::now()->subYear(),
        };

        $query = Order::query();
        $this->orderService->applyRoleFilter($query, $user);

        if ($period === 'today') {
            return [[
                'index' => 0,
                'label' => Carbon::now()->format('d/m'),
                'count' => (int) $query->whereDate('created_at', Carbon::today())->count()
            ]];
        }

        $data = $query->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date');

        $result = [];
        $days = match ($period) {
            'week' => 7,
            'month' => 30,
            default => 1,
        };

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $result[] = [
                'index' => ($days - 1) - $i,
                'label' => $date,
                'count' => (int) ($data[$date] ?? 0)
            ];
        }
        return $result;
    }

    public function getFinancialAnalysis(User $user, string $period = 'month'): array
    {
        $result = [];
        $periods = match ($period) {
            'today' => [Carbon::now()->startOfDay()],
            'week' => array_map(fn($i) => Carbon::now()->subDays($i), range(6, 0)),
            'month' => array_map(fn($i) => Carbon::now()->subDays($i), range(29, 0)),
            default => [],
        };

        if (empty($periods)) return [];

        foreach ($periods as $date) {
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();

            $orderQuery = Order::query();
            $this->orderService->applyRoleFilter($orderQuery, $user);
            $allowedOrderIds = $orderQuery->pluck('id');

            $bookingQuery = Booking::query();
            $this->bookingService->applyRoleFilter($bookingQuery, $user);
            $allowedBookingIds = $bookingQuery->pluck('id');

            $invoiceIds = Invoice::where(function ($q) use ($allowedOrderIds) {
                $q->where('invoiceable_type', Order::class)->whereIn('invoiceable_id', $allowedOrderIds);
            })->orWhere(function ($q) use ($allowedBookingIds) {
                $q->where('invoiceable_type', Booking::class)->whereIn('invoiceable_id', $allowedBookingIds);
            })->pluck('id');

            $revenue = (float) PaymentAllocation::whereIn('invoice_id', $invoiceIds)
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount_applied');

            $profit = $this->calculatePeriodProfit($start, $end, $user);

            $refunds = (float) Refund::where(function ($q) use ($allowedOrderIds, $allowedBookingIds) {
                $q->whereIn('order_id', $allowedOrderIds)->orWhereIn('booking_id', $allowedBookingIds);
            })
                ->where('status', RefundStatus::Completed)
                ->whereBetween('processed_at', [$start, $end])
                ->sum('amount');

            $result[] = [
                'index' => count($result),
                'label' => $date->format('d/m'),
                'revenue' => $revenue,
                'profit' => $profit,
                'refunds' => $refunds,
            ];
        }

        return $result;
    }

    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'today' => Carbon::now()->startOfDay(),
            'week' => Carbon::now()->subDays(7),
            'month' => Carbon::now()->subDays(30),
            'quarter' => Carbon::now()->subMonths(3)->startOfMonth(),
            default => Carbon::now()->subYear(),
        };
    }

    public function getOrderStatusDistribution(User $user, string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        $query = Order::query();
        $this->orderService->applyRoleFilter($query, $user);

        $orders = $query->where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status->value => $item->count])
            ->toArray();

        return collect(OrderStatus::cases())->map(fn($status) => [
            'key' => $status->label(),
            'color' => $status->color(),
            'value' => (int) ($orders[$status->value] ?? 0),
        ])->toArray();
    }

    public function getRefundStatusDistribution(User $user, string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        $orderQuery = Order::query();
        $this->orderService->applyRoleFilter($orderQuery, $user);
        $allowedOrderIds = $orderQuery->pluck('id');

        $bookingQuery = Booking::query();
        $this->bookingService->applyRoleFilter($bookingQuery, $user);
        $allowedBookingIds = $bookingQuery->pluck('id');

        $refunds = Refund::where(function ($q) use ($allowedOrderIds, $allowedBookingIds) {
            $q->whereIn('order_id', $allowedOrderIds)->orWhereIn('booking_id', $allowedBookingIds);
        })
            ->whereBetween('created_at', [$startDate, now()])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status->value => $item->count])
            ->toArray();

        return collect(\App\Enums\RefundStatus::cases())->map(fn($status) => [
            'key' => $status->label(),
            'color' => $status->color() ?? '#ef4444',
            'value' => (int) ($refunds[$status->value] ?? 0),
        ])->toArray();
    }

    public function getBookingStatusDistribution(User $user, string $period = 'month'): array
    {
        $startDate = $this->getStartDate($period);

        $query = Booking::query();
        $this->bookingService->applyRoleFilter($query, $user);

        $stats = $query->where('created_at', '>=', $startDate)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status->value => $item->count])
            ->toArray();

        return collect(BookingStatus::cases())->map(fn($status) => [
            'key' => $status->label(),
            'color' => $status->color() ?? '#6366f1',
            'value' => (int) ($stats[$status->value] ?? 0),
        ])->toArray();
    }

    public function getRecentOrders(User $user): array
    {
        $query = Order::query();
        $this->orderService->applyRoleFilter($query, $user);

        return $query->with(['customer'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'number' => $order->order_number,
                'customer' => $order->customer?->full_name ?? 'Guest',
                'total' => (float)$order->total_amount,
                'status' => $order->status->label(),
                'created_at' => $order->created_at->format('d M Y H:i'),
            ])
            ->toArray();
    }

    public function getRecentBookings(User $user): array
    {
        $query = Booking::query();
        $this->bookingService->applyRoleFilter($query, $user);

        return $query->with(['customer', 'designer'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($booking) => [
                'id' => $booking->id,
                'customer' => $booking->customer_name ?? ($booking->customer?->full_name ?? 'Khách vãng lai'),
                'start_at' => $booking->start_at ? Carbon::parse($booking->start_at)->format('d M Y H:i') : 'N/A',
                'status' => $booking->status->label(),
            ])
            ->toArray();
    }

    public function getLowStockItems(User $user): array
    {
        $query = Inventory::query();
        $this->locationService->applyRoleFilter($query, $user);

        return $query->with(['variant.product'])
            ->where('quantity', '<', 5)
            ->get()
            ->map(fn($inv) => [
                'variant' => $inv->variant->name ?? 'Unknown Product',
                'product' => $inv->variant->product->name ?? 'Unknown Product',
                'quantity' => $inv->quantity,
                'location' => $inv->location->name ?? 'Unknown Location',
            ])
            ->toArray();
    }
}
