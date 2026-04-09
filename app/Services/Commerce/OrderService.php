<?php

namespace App\Services\Commerce;

use App\Data\Commerce\OrderFilterData;
use App\Enums\OrderStatus;
use App\Models\Auth\User;
use App\Models\Commerce\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderService
{
    public function getFiltered(OrderFilterData $filter): LengthAwarePaginator
    {
        return Order::query()
            ->with(['customer', 'acceptedBy', 'items'])
            ->when($filter->customer_id, fn ($q) => $q->where('customer_id', $filter->customer_id))
            ->when($filter->status, fn ($q) => $q->where('status', $filter->status))
            ->when($filter->search, fn ($q) => $q->where('order_number', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(OrderFilterData $filter): LengthAwarePaginator
    {
        return Order::onlyTrashed()
            ->with(['customer', 'acceptedBy'])
            ->when($filter->customer_id, fn ($q) => $q->where('customer_id', $filter->customer_id))
            ->when($filter->search, fn ($q) => $q->where('order_number', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Order
    {
        return Order::with(['customer', 'shippingAddress', 'items', 'acceptedBy'])->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return OrderStatus::options();
    }

    public function getCustomerOptions(): Collection
    {
        return User::query()
            ->where('type', 'customer')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn ($user) => [
                'id' => $user->id,
                'label' => $user->name.' ('.$user->email.')',
            ]);
    }
}
