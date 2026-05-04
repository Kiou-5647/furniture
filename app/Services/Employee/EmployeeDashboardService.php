<?php

namespace App\Services\Employee;

use App\Enums\OrderStatus;
use App\Models\Auth\User;
use App\Models\Sales\Order;
use App\Models\Booking\Booking;
use App\Models\Inventory\Inventory;
use App\Models\Sales\Refund;
use App\Models\Sales\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeDashboardService
{
    public function getData(User $user, string $period = 'year'): array
    {
        return [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->toArray(),
                'permissions' => $user->getPermissionNames()->toArray(),
            ],
            'employee' => $user->employee,
            'period' => $period,
            'summary' => $this->getSummary($user, $period),
            'charts' => [
                'orders_trend' => $this->getOrdersTrend($user, $period),
                'financial_analysis' => $this->getFinancialAnalysis($user, $period),
                'order_distribution' => $this->getOrderStatusDistribution(),
                'booking_distribution' => $this->getBookingStats(),
            ],
            'tables' => [
                'recent_orders' => $this->getRecentOrders(),
                'recent_bookings' => $this->getRecentBookings(),
                'low_stock' => $this->getLowStockItems(),
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

        $customersCount = \App\Models\Auth\User::count();

        $ordersCurrent = \App\Models\Sales\Order::whereBetween('created_at', [$start, $end])->count();
        $ordersPrev = \App\Models\Sales\Order::whereBetween('created_at', [$prevStart, $start])->count();

        $revenueCurrent = (float) \App\Models\Sales\PaymentAllocation::whereHas('payment', function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })
            ->sum('amount_applied');
        $revenuePrev = (float) \App\Models\Sales\PaymentAllocation::whereHas('payment', function ($q) use ($prevStart, $start) {
            $q->whereBetween('created_at', [$prevStart, $start]);
        })
            ->sum('amount_applied');

        $profitCurrent = $this->calculatePeriodProfit($start, $end);
        $profitPrev = $this->calculatePeriodProfit($prevStart, $start);

        return [
            'customers' => [
                'value' => $customersCount,
                'trend' => 0,
            ],
            'orders' => [
                'value' => $ordersCurrent,
                'trend' => $this->calculateTrend($ordersPrev, $ordersCurrent),
            ],
            'revenue' => [
                'value' => $revenueCurrent,
                'trend' => $this->calculateTrend($revenuePrev, $revenueCurrent),
            ],
            'profit' => [
                'value' => $profitCurrent,
                'trend' => $this->calculateTrend($profitPrev, $profitCurrent),
            ],
        ];
    }

    protected function calculateTrend($old, $new): float
    {
        if ($old == 0) return $new > 0 ? 100 : 0;
        return (($new - $old) / $old) * 100;
    }

    public function calculatePeriodProfit(\DateTimeInterface $start, \DateTimeInterface $end): float
    {
        // 1. Revenue: Actual cash received in this period (System-wide)
        $revenue = (float) \App\Models\Sales\PaymentAllocation::whereHas('payment', function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })
            ->sum('amount_applied');

        // 2. COGS: Matching Principle - Costs associated with orders paid in this period (System-wide)
        $cogs = \App\Models\Sales\OrderItem::whereHas('order', function ($query) use ($start, $end) {
            $query->whereNotNull('paid_at')
                ->whereBetween('paid_at', [$start, $end]);
        })
            ->get()
            ->sum(function ($item) {
                return ($item->cost_per_unit ?? 0) * $item->quantity;
            });

        // 3. Refunds: Matching Principle - Refunds for orders paid in this period
        $refunds = (float) \App\Models\Sales\Refund::whereHas('order', function ($query) use ($start, $end) {
            $query->whereNotNull('paid_at')
                ->whereBetween('paid_at', [$start, $end]);
        })
            ->where('status', \App\Enums\RefundStatus::Completed)
            ->sum('amount');

        // 4. Recovered Cost: Matching Principle - Value of assets returned from orders paid in this period
        $recoveredCost = \App\Models\Fulfillment\ShipmentItem::whereHas('orderItem.order', function ($query) use ($start, $end) {
            $query->whereNotNull('paid_at')
                ->whereBetween('paid_at', [$start, $end]);
        })
            ->where('status', \App\Enums\ShipmentStatus::Returned)
            ->get()
            ->sum(function ($item) {
                $orderItem = $item->orderItem;
                if (!$orderItem) return 0;

                if ($orderItem->purchasable_type === \App\Models\Product\Bundle::class && !empty($orderItem->configuration)) {
                    foreach ($orderItem->configuration as $config) {
                        if (($config['variant_id'] ?? null) === $item->variant_id) {
                            return ($config['cost'] ?? 0) * $item->quantity_shipped;
                        }
                    }
                }

                return ($orderItem->cost_per_unit ?? 0) * $item->quantity_shipped;
            });

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

        if ($period === 'today') {
            return [[
                'index' => 0,
                'label' => Carbon::now()->format('d/m'),
                'count' => (int) \App\Models\Sales\Order::whereDate('created_at', Carbon::today())->count()
            ]];
        }

        if ($period === 'week') {
            $data = \App\Models\Sales\Order::where('created_at', '>=', $startDate)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->pluck('count', 'date');

            $result = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $result[] = [
                    'index' => 6 - $i,
                    'label' => $date,
                    'count' => (int) ($data[$date] ?? 0)
                ];
            }
            return $result;
        }

        if ($period === 'month') {
            $data = \App\Models\Sales\Order::where('created_at', '>=', $startDate)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->pluck('count', 'date');

            $result = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $result[] = [
                    'index' => 29 - $i,
                    'label' => $date,
                    'count' => (int) ($data[$date] ?? 0)
                ];
            }
            return $result;
        }

        if ($period === 'quarter') {
            $startDate = Carbon::now()->subMonths(3)->startOfMonth();
            $data = \App\Models\Sales\Order::where('created_at', '>=', $startDate)
                ->select(DB::raw('EXTRACT(MONTH FROM created_at) as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->pluck('count', 'month');

            $result = [];
            $idx = 0;
            for ($m = $startDate->month; $m <= Carbon::now()->month; $m++) {
                $monthName = Carbon::create(null, $m, 1)->format('M');
                $result[] = [
                    'index' => $idx++,
                    'label' => $monthName,
                    'count' => (int) ($data[$m] ?? 0)
                ];
            }
            return $result;
        }

        $year = date('Y');
        $data = \App\Models\Sales\Order::whereYear('created_at', $year)
            ->select(DB::raw('EXTRACT(MONTH FROM created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->pluck('count', 'month');

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'index' => $m - 1,
                'label' => 'T' . $m,
                'count' => (int) ($data[$m] ?? 0)
            ];
        }
        return $result;
    }

    public function getFinancialAnalysis(User $user, string $period = 'month'): array
    {
        if ($period === 'today') {
            $start = Carbon::now()->startOfDay();
            $end = Carbon::now()->endOfDay();
            return [[
                'index' => 0,
                'label' => Carbon::now()->format('d/m'),
                'revenue' => (float) \App\Models\Sales\PaymentAllocation::whereHas('payment', function ($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                })
                    ->sum('amount_applied'),
                'profit' => $this->calculatePeriodProfit($start, $end),
                'refunds' => (float) \App\Models\Sales\Refund::whereHas('order', function ($query) use ($start, $end) {
                    $query->whereNotNull('paid_at')
                        ->whereBetween('paid_at', [$start, $end]);
                })
                    ->where('status', \App\Enums\RefundStatus::Completed)
                    ->sum('amount'),
            ]];
        }

        if ($period === 'week') {
            $startDate = Carbon::now()->subDays(6);
            $result = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();

                $revenue = (float) \App\Models\Sales\PaymentAllocation::whereHas('payment', function ($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                })
                    ->sum('amount_applied');
                $profit = $this->calculatePeriodProfit($start, $end);
                $refunds = (float) \App\Models\Sales\Refund::whereHas('order', function ($query) use ($start, $end) {
                    $query->whereNotNull('paid_at')
                        ->whereBetween('paid_at', [$start, $end]);
                })
                    ->where('status', \App\Enums\RefundStatus::Completed)
                    ->sum('amount');

                $result[] = [
                    'index' => 6 - $i,
                    'label' => $date->format('d/m'),
                    'revenue' => $revenue,
                    'profit' => $profit,
                    'refunds' => $refunds,
                ];
            }
            return $result;
        }

        if ($period === 'month') {
            $startDate = Carbon::now()->subDays(30);
            $result = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();

                $revenue = (float) \App\Models\Sales\PaymentAllocation::whereHas('payment', function ($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                })
                    ->sum('amount_applied');
                $profit = $this->calculatePeriodProfit($start, $end);
                $refunds = (float) \App\Models\Sales\Refund::whereHas('order', function ($query) use ($start, $end) {
                    $query->whereNotNull('paid_at')
                        ->whereBetween('paid_at', [$start, $end]);
                })
                    ->where('status', \App\Enums\RefundStatus::Completed)
                    ->sum('amount');

                $result[] = [
                    'index' => 29 - $i,
                    'label' => $date->format('d/m'),
                    'revenue' => $revenue,
                    'profit' => $profit,
                    'refunds' => $refunds,
                ];
            }
            return $result;
        }

        if ($period === 'quarter') {
            $startDate = Carbon::now()->subMonths(3)->startOfMonth();
            $result = [];
            $idx = 0;
            $currentMonth = Carbon::now()->month;
            for ($m = $startDate->month; $m <= $currentMonth; $m++) {
                $year = ($m <= Carbon::now()->month && Carbon::now()->month >= 1) ? Carbon::now()->year : Carbon::now()->year - 1;
                $start = Carbon::create($year, $m, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();

                $revenue = (float) \App\Models\Sales\PaymentAllocation::whereHas('payment', function ($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                })
                    ->sum('amount_applied');
                $profit = $this->calculatePeriodProfit($start, $end);
                $refunds = (float) \App\Models\Sales\Refund::whereHas('order', function ($query) use ($start, $end) {
                    $query->whereNotNull('paid_at')
                        ->whereBetween('paid_at', [$start, $end]);
                })
                    ->where('status', \App\Enums\RefundStatus::Completed)
                    ->sum('amount');

                $result[] = [
                    'index' => $idx++,
                    'label' => $start->format('m'),
                    'revenue' => $revenue,
                    'profit' => $profit,
                    'refunds' => $refunds,
                ];
            }
            return $result;
        }

        $year = date('Y');
        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $revenue = (float) \App\Models\Sales\PaymentAllocation::whereHas('payment', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
                ->sum('amount_applied');
            $profit = $this->calculatePeriodProfit($start, $end);
            $refunds = (float) \App\Models\Sales\Refund::whereHas('order', function ($query) use ($start, $end) {
                $query->whereNotNull('paid_at')
                    ->whereBetween('paid_at', [$start, $end]);
            })
                ->where('status', \App\Enums\RefundStatus::Completed)
                ->sum('amount');

            $result[] = [
                'index' => $m - 1,
                'label' => $start->format('m'),
                'revenue' => $revenue,
                'profit' => $profit,
                'refunds' => $refunds,
            ];
        }
        return $result;
    }

    protected function getOrderStatusDistribution(): array
    {
        $orders = Order::where('created_at', '>=', Carbon::now()->subDays(30))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [
                $item->status->value => $item->count
            ])
            ->toArray();

        return collect(OrderStatus::cases())->map(fn($status) => [
            'status' => $status->label(),
            'color' => $status->color(),
            'count' => (int) ($orders[$status->value] ?? 0),
        ])->toArray();
    }

    protected function getSalesByCategory(): array
    {
        return DB::table('order_items')
            ->join('product_variants', 'order_items.purchasable_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.display_name', DB::raw('COUNT(*) as total_sales'))
            ->groupBy('categories.display_name')
            ->orderByDesc('total_sales')
            ->get()
            ->map(fn($item) => [
                'category' => $item->display_name,
                'value' => (int) $item->total_sales,
            ])
            ->toArray();
    }

    protected function getBookingStats(): array
    {
        return Booking::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn($item) => [
                'status' => $item->status,
                'count' => (int)$item->count,
            ])
            ->toArray();
    }

    public function getRecentOrders(): array
    {
        return Order::with(['customer'])
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

    public function getRecentBookings(): array
    {
        return Booking::with(['customer', 'designer'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($booking) => [
                'id' => $booking->id,
                'customer' => $booking->designer?->full_name ?? 'Unassigned',
                'start_at' => $booking->start_at ? Carbon::parse($booking->start_at)->format('d M Y H:i') : 'N/A',
                'status' => $booking->status,
            ])
            ->toArray();
    }

    public function getLowStockItems(): array
    {
        return Inventory::with(['variant.product'])
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
