<?php

namespace App\Services\Finance;

use App\Data\Finance\PaymentFilterData;
use App\Enums\PaymentStatus;
use App\Models\Finance\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaymentService
{
    public function getFiltered(PaymentFilterData $filter): LengthAwarePaginator
    {
        return Payment::query()
            ->with(['customer', 'allocations.invoice'])
            ->when($filter->status, fn ($q) => $q->where('status', $filter->status))
            ->when($filter->customer_id, fn ($q) => $q->where('customer_id', $filter->customer_id))
            ->when($filter->gateway, fn ($q) => $q->where('gateway', $filter->gateway))
            ->when($filter->search, fn ($q) => $q->where('transaction_id', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Payment
    {
        return Payment::with(['customer', 'allocations.invoice'])
            ->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return PaymentStatus::options();
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
}
