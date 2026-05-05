<?php

namespace App\Services\Customer;

use App\Data\Customer\CustomerFilterData;
use App\Models\Customer\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    public function getFiltered(CustomerFilterData $filter): LengthAwarePaginator
    {
        return Customer::query()
            ->with('user')
            ->when(! is_null($filter->is_active), function ($query) use ($filter) {
                $query->whereHas('user', fn($q) => $q->where('is_active', $filter->is_active));
            })
            ->when($filter->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'ilike', "%{$search}%")
                        ->orWhere('phone', 'ilike', "%{$search}%")
                        ->orWhereHas('user', fn($uq) => $uq->where('email', 'ilike', "%{$search}%"));
                });
            })
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Customer
    {
        return Customer::query()
            ->with(['orders' => fn($q) => $q->latest()->limit(10)])
            ->findOrFail($id);
    }
}
