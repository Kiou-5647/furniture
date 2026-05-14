<?php

namespace App\Services\Sales;

use App\Constants\Permission;
use App\Data\Sales\RefundFilterData;
use App\Models\Auth\User;
use App\Models\Sales\Refund;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class RefundService
{
    public function applyRoleFilter(Builder $query, User $user)
    {
        if ($user->hasAnyRole(['Quản trị viên', 'Quản lý'])) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereRaw('1 = 0');

            if ($user->isEmployee() && $user->is_active && $user->hasPermissionTo(Permission::REFUND['SELECT'])) {
                $employee = $user->employee;

                // Quản lý cửa hàng
                if ($user->hasRole('Quản lý cửa hàng')) {
                    $q->orWhere(function ($sub) use ($employee) {
                        // Thấy các đơn hoàn tiền trong chi nhánh mình quản lý
                        $sub->whereHas('order.storeLocation', function ($qOrderLocation) use ($employee) {
                            $qOrderLocation->where('id', $employee?->store_location_id)
                                ->orWhere('manager_id', $employee?->id);
                        });
                    });
                } else {
                    // Nhân viên
                    // Đơn hoàn tiền mình yêu cầu
                    $q->orWhere('requested_by', $employee?->id)
                        // OR Đơn mình xử lý
                        ->orWhere('processed_by', $employee?->id)
                        // OR của đơn hàng mình xử lý
                        ->orWhereHas('order', fn ($qOrder) => $qOrder->where('accepted_by', $employee?->id))
                        // OR của lịch hẹn mình xử lý
                        ->orWhereHas('booking', fn ($qBooking) => $qBooking->where('designer_id', $employee?->designer?->id));
                }
            }
        });
    }

    public function getFiltered(RefundFilterData $filter, User $user): LengthAwarePaginator
    {
        $query = Refund::query()
            ->with(['order', 'booking', 'payment', 'invoice', 'requestedBy', 'processedBy']);

        $query = $this->applyRoleFilter($query, $user);

        return $query->when($filter->status, fn ($q) => $q->where('status', $filter->status))
            ->when($filter->order_id, fn ($q) => $q->where('order_id', $filter->order_id))
            ->when($filter->search, fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('order_number', 'ilike', "%{$filter->search}%")))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getById(string $id, User $user): Refund
    {
        $query = Refund::with([
            'order',
            'booking',
            'invoice',
            'requestedBy',
            'processedBy',
        ]);

        return $this->applyRoleFilter($query, $user)->findOrFail($id);
    }
}
