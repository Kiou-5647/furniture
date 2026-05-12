<?php

namespace App\Services\Sales;

use App\Constants\Permission;
use App\Data\Sales\InvoiceFilterData;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Auth\User;
use App\Models\Booking\Booking;
use App\Models\Hr\Employee;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use App\Services\Booking\BookingService;
use App\Services\Sales\OrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InvoiceService
{
    public function __construct(
        protected OrderService $orderService,
        protected BookingService $bookingService,
    ) {}

    public function applyRoleFilter(Builder $query, User $user)
    {
        // Quản lý chung: Thấy tất cả
        if ($user->hasAnyRole(['Quản trị viên', 'Quản lý'])) {
            return $query;
        }

        // Khách hàng: Chỉ thấy hóa đơn của mình
        if ($user->isCustomer()) {
            return $query->where(function ($q) use ($user) {
                $q->whereHas('invoiceable', function ($sub) use ($user) {
                    $sub->where('customer_id', $user->customer->id);
                });
            });
        }

        return $query->where(function ($q) use ($user) {
            $q->whereRaw('1 = 0');

            if ($user->isEmployee() && $user->employee?->is_active && $user->hasPermissionTo(Permission::INVOICE['SELECT'])) {
                $employee = $user->employee;

                // Quản lý cửa hàng
                if ($employee->hasRole('Quản lý cửa hàng')) {
                    $q->orWhere(function ($sub) use ($employee) {
                        // Check hóa đơn có
                        $sub->whereHas('invoiceable', function ($qOrder) use ($employee) {
                            // Có đơn hàng thuộc cửa hàng
                            $qOrder->where('store_location_id', $employee->store_location_id)
                                // OR
                                // Được duyệt bởi nhân viên của cửa hàng
                                ->orWhereHas('acceptedBy', function ($acceptedBy) use ($employee) {
                                    $acceptedBy->where('store_location_id', $employee->store_location_id);
                                });
                        });
                    });
                } else {
                    // Nhân viên
                    // Hóa đơn mình xác nhận
                    $q->orWhere('validated_by', $employee?->id);
                    // OR
                    $q->orWhere(function ($sub) use ($employee) {
                        // Hóa đơn gắn với đơn hàng mình tiếp nhận
                        $sub->whereHas('invoiceable', function ($qOrder) use ($employee) {
                            $qOrder->where('accepted_by', $employee?->id);
                        });
                        // OR
                        // Hóa đơn gắn với lịch hẹn mình là designer
                        $sub->orWhereHas('invoiceable', function ($qBooking) use ($employee) {
                            $qBooking->where('designer_id', $employee?->designer?->id);
                        });
                    });
                }
            }
        });
    }
    public function getFiltered(InvoiceFilterData $filter, User $user): LengthAwarePaginator
    {
        $allowedSortColumns = ['invoice_number', 'amount_due', 'amount_paid', 'created_at', 'updated_at'];
        $orderBy = in_array($filter->order_by, $allowedSortColumns, true) ? $filter->order_by : 'created_at';
        $orderDirection = $filter->order_direction === 'asc' ? 'asc' : 'desc';

        $query = Invoice::query()
            ->with(['invoiceable', 'validatedBy', 'allocations']);

        $this->applyRoleFilter($query, $user);

        return $query->when($filter->status, fn($q) => $q->byStatus($filter->status))
            ->when($filter->type, fn($q) => $q->byType($filter->type))
            ->when($filter->invoiceable_type, fn($q) => $q->byInvoiceableType($filter->invoiceable_type))
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->orderBy($orderBy, $orderDirection)
            ->paginate($filter->per_page);
    }

    public function getById(string $id, User $user): Invoice
    {
        $query = Invoice::with(['invoiceable', 'validatedBy', 'allocations.payment']);
        return $this->applyRoleFilter($query, $user)->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return InvoiceStatus::options();
    }

    public function getTypeOptions(): array
    {
        return InvoiceType::options();
    }

    public function getEmployeeOptions(): Collection
    {
        return Employee::query()
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->orderBy('full_name')
            ->get(['id', 'full_name'])
            ->map(fn($emp) => [
                'id' => $emp->id,
                'label' => $emp->full_name,
            ]);
    }

    public function getOrderOptions(User $user): Collection
    {
        $query = Order::query()
            ->whereNotIn('status', ['cancelled'])
            ->whereDoesntHave('invoices')
            ->with('customer');

        // Sử dụng logic phân quyền từ OrderService
        $query = $this->orderService->applyRoleFilter($query, $user);

        return $query->orderByDesc('created_at')
            ->get(['id', 'order_number', 'total_amount', 'status'])
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'customer_name' => $order->customer?->full_name ?? $order->customer?->name ?? '—',
            ]);
    }

    public function getBookingOptions(User $user): Collection
    {
        $query = Booking::query()
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) {
                $q->whereNull('deposit_invoice_id')
                    ->orWhereNull('final_invoice_id');
            })
            ->with(['customer', 'designer']);

        // Sử dụng logic phân quyền từ BookingService
        $query = $this->bookingService->applyRoleFilter($query, $user);

        return $query->orderByDesc('created_at')
            ->get()
            ->map(fn($booking) => [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
                'customer_name' => $booking->customer_name ?? '—',
                'designer_name' => $booking->designer?->full_name ?? '—',
                'total_amount' => (string) $booking->total_price,
                'has_deposit' => !is_null($booking->deposit_invoice_id),
                'has_final' => !is_null($booking->final_invoice_id),
            ]);
    }
}
