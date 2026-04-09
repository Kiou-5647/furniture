<?php

namespace App\Services\Sales;

use App\Data\Sales\InvoiceFilterData;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Sales\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceService
{
    public function getFiltered(InvoiceFilterData $filter): LengthAwarePaginator
    {
        return Invoice::query()
            ->with(['invoiceable', 'validatedBy', 'allocations'])
            ->when($filter->status, fn ($q) => $q->where('status', $filter->status))
            ->when($filter->type, fn ($q) => $q->where('type', $filter->type))
            ->when($filter->invoiceable_type, fn ($q) => $q->where('invoiceable_type', $filter->invoiceable_type))
            ->when($filter->search, fn ($q) => $q->where('invoice_number', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by, $filter->order_direction)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(InvoiceFilterData $filter): LengthAwarePaginator
    {
        return Invoice::onlyTrashed()
            ->with(['invoiceable', 'validatedBy'])
            ->when($filter->search, fn ($q) => $q->where('invoice_number', 'ilike', "%{$filter->search}%"))
            ->orderBy($filter->order_by ?? 'deleted_at', $filter->order_direction ?? 'desc')
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Invoice
    {
        return Invoice::with(['invoiceable', 'validatedBy', 'allocations.payment'])
            ->findOrFail($id);
    }

    public function getStatusOptions(): array
    {
        return InvoiceStatus::options();
    }

    public function getTypeOptions(): array
    {
        return InvoiceType::options();
    }
}
