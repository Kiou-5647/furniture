<?php

namespace App\Services\Sales;

use App\Data\Sales\PaymentFilterData;
use App\Models\Sales\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaymentService
{
    public function getFiltered(PaymentFilterData $filter): LengthAwarePaginator
    {
        $allowedSortColumns = ['transaction_id', 'amount', 'gateway', 'created_at', 'updated_at'];
        $orderBy = in_array($filter->order_by, $allowedSortColumns, true) ? $filter->order_by : 'created_at';
        $orderDirection = $filter->order_direction === 'asc' ? 'asc' : 'desc';

        return Payment::query()
            ->with(['customer', 'allocations.invoice'])
            ->when($filter->customer_id, fn ($q) => $q->where('customer_id', $filter->customer_id))
            ->when($filter->gateway, fn ($q) => $q->where('gateway', $filter->gateway))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($orderBy, $orderDirection)
            ->paginate($filter->per_page);
    }

    public function getGatewayOptions(): Collection
    {
        return Payment::query()
            ->distinct()
            ->orderBy('gateway')
            ->pluck('gateway')
            ->map(fn ($gateway) => [
                'id' => $gateway,
                'label' => ucfirst($gateway),
            ]);
    }

    public function getCustomerOptions(): Collection
    {
        return Payment::query()
            ->join('users', 'payments.customer_id', '=', 'users.id')
            ->leftJoin('customers', 'users.id', '=', 'customers.user_id')
            ->distinct()
            ->select('users.id', 'customers.full_name', 'users.name')
            ->orderBy('customers.full_name')
            ->get()
            ->map(fn ($payment) => [
                'id' => $payment->id,
                'label' => $payment->full_name ?? $payment->name ?? 'Unknown',
            ]);
    }
}
