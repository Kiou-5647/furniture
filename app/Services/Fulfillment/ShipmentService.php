<?php

namespace App\Services\Fulfillment;

use App\Constants\Permission;
use App\Data\Fulfillment\ShipmentFilterData;
use App\Models\Auth\User;
use App\Models\Fulfillment\Shipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ShipmentService
{
    public function applyRoleFilter(Builder $query, User $user)
    {
        // 1. Quản lý chung: Thấy hết
        if ($user->hasAnyRole(['Quản trị viên', 'Quản lý'])) {
            return $query;
        }

        // 2. Khách hàng: Chỉ thấy đơn của mình
        if ($user->isCustomer()) {
            return $query->whereHas('order', function ($sub) use ($user) {
                $sub->where('customer_id', $user->customer?->id);
            });
        }

        // 3. Nhân viên: Cần dùng closure để kết hợp các điều kiện OR phức tạp
        return $query->where(function ($q) use ($user) {
            $q->whereRaw('1 = 0');

            if ($user->isEmployee() && $user->is_active && $user->hasPermissionTo(Permission::SHIPMENT['SELECT'])) {
                $employee = $user->employee;

                // Quản lý kho hàng
                if ($user->hasRole('Quản lý kho hàng')) {
                    $q->orWhere(function ($sub) use ($employee) {
                        // Xuất kho từ chi nhánh
                        $sub->where('origin_location_id', $employee?->store_location_id)
                            // OR từ kho
                            ->orWhere('origin_location_id', $employee?->warehouse_location_id);
                    });
                } else {
                    // Nhân viên
                    // Đơn mình xử lý
                    $q->orWhere('handled_by', $employee?->id);

                    // OR
                    $q->orWhere(function ($sub) use ($employee) {
                        // Đơn chưa ai nhận
                        $sub->whereNull('handled_by');
                        // AND
                        $sub->where(function ($q2) use ($employee) {
                            // Đúng chi nhánh
                            $q2->where('origin_location_id', $employee?->store_location_id)
                                // OR đúng kho
                                ->orWhere('origin_location_id', $employee?->warehouse_location_id);
                        });
                    });
                }
            }
        });
    }

    public function getFiltered(ShipmentFilterData $filter, User $user): LengthAwarePaginator
    {
        $query = Shipment::query()
            ->with(['order', 'originLocation', 'shippingMethod', 'handledBy', 'items'])
            ->when($filter->order_id, fn ($q) => $q->where('order_id', $filter->order_id))
            ->when($filter->status, fn ($q) => $q->where('status', $filter->status))
            ->when($filter->search, fn ($q) => $q->where('shipment_number', 'ilike', "%{$filter->search}%"));

        $query = $this->applyRoleFilter($query, $user);

        return $query->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getById(string $id, User $user): Shipment
    {
        $query = Shipment::with([
            'order.customer',
            'originLocation',
            'shippingMethod',
            'handledBy',
            'items.orderItem.purchasable',
            'items.variant',
        ]);

        return $this->applyRoleFilter($query, $user)->findOrFail($id);
    }
}
