<?php

namespace App\Services\Booking;

use App\Constants\Permission;
use App\Data\Booking\BookingFilterData;
use App\Models\Auth\User;
use App\Models\Booking\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BookingService
{
    public function applyRoleFilter(Builder $query, User $user): Builder
    {
        // Quản lý: Thấy tất cả
        if ($user->hasAnyRole(['Quản trị viên', 'Quản lý'])) {
            return $query;
        }

        // Khách hàng: Chỉ thấy đơn của mình
        if ($user->isCustomer()) {
            return $query->where('customer_id', $user->customer?->id);
        }

        return $query->where(function ($q) use ($user) {
            $q->whereRaw('1 = 0');

            if ($user->isEmployee() && $user->designer?->is_active && $user->hasPermissionTo(Permission::BOOKING['SELECT'])) {
                // Nhân viên
                // Đơn của chính mình
                $q->orWhere('designer_id', $user->designer->id);
            }
        });
    }

    public function getById(string $id, User $user): Booking
    {
        $query = Booking::with([
            'customer',
            'designer.user',
            'designer.employee',
            'depositInvoice',
            'finalInvoice',
        ]);

        return $this->applyRoleFilter($query, $user)->findOrFail($id);
    }

    public function getFiltered(BookingFilterData $filter, User $user): LengthAwarePaginator
    {
        $query = Booking::query()
            ->with(['customer', 'designer', 'depositInvoice', 'finalInvoice'])
            ->when($filter->designer_id, fn($q) => $q->byDesigner($filter->designer_id))
            ->when($filter->status, fn($q) => $q->byStatus($filter->status))
            ->when($filter->customer_id, fn($q) => $q->byCustomer($filter->customer_id))
            ->when($filter->search, fn($q) => $q->search($filter->search));

        $query = $this->applyRoleFilter($query, $user);

        return $query->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getCustomerOptions(): Collection
    {
        return User::query()
            ->where('type', 'customer')
            ->where('is_active', true)
            ->with('customer')
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn($user) => [
                'id' => $user->customer->id,
                'name' => $user->customer?->full_name ?? $user->name,
                'email' => $user->customer?->user?->email ?? $user->email,
                'phone' => $user->customer?->phone,
                'address' => [
                    'province_code' => $user->customer?->province_code,
                    'province_name' => $user->customer?->province_name,
                    'ward_code' => $user->customer?->ward_code,
                    'ward_name' => $user->customer?->ward_name,
                    'street' => $user->customer?->street,
                ],
            ]);
    }
}
