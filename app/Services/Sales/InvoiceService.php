<?php

namespace App\Services\Sales;

use App\Data\Sales\InvoiceFilterData;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
// use App\Models\Design\Booking;
use App\Models\Hr\Employee;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InvoiceService
{
    public function getFiltered(InvoiceFilterData $filter): LengthAwarePaginator
    {
        $allowedSortColumns = ['invoice_number', 'amount_due', 'amount_paid', 'created_at', 'updated_at'];
        $orderBy = in_array($filter->order_by, $allowedSortColumns, true) ? $filter->order_by : 'created_at';
        $orderDirection = $filter->order_direction === 'asc' ? 'asc' : 'desc';

        return Invoice::query()
            ->with(['invoiceable', 'validatedBy', 'allocations'])
            ->when($filter->status, fn ($q) => $q->byStatus($filter->status))
            ->when($filter->type, fn ($q) => $q->byType($filter->type))
            ->when($filter->invoiceable_type, fn ($q) => $q->byInvoiceableType($filter->invoiceable_type))
            ->when($filter->search, fn ($q) => $q->search($filter->search))
            ->orderBy($orderBy, $orderDirection)
            ->paginate($filter->per_page);
    }

    public function getTrashedFiltered(InvoiceFilterData $filter): LengthAwarePaginator
    {
        return Invoice::onlyTrashed()
            ->with(['invoiceable', 'validatedBy'])
            ->when($filter->search, fn ($q) => $q->search($filter->search))
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

    public function getEmployeeOptions(): Collection
    {
        return Employee::query()
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->orderBy('full_name')
            ->get(['id', 'full_name'])
            ->map(fn ($emp) => [
                'id' => $emp->id,
                'label' => $emp->full_name,
            ]);
    }

    public function getOrderOptions(): Collection
    {
        return Order::query()
            ->whereNotIn('status', ['cancelled'])
            ->whereDoesntHave('invoices')
            ->with('customer.customer')
            ->orderByDesc('created_at')
            ->get(['id', 'order_number', 'total_amount', 'status'])
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'customer_name' => $order->customer?->customer?->full_name ?? $order->customer?->name ?? '—',
            ]);
    }

    // TODO: Implement when Booking model exists
    // public function getBookingOptions(): Collection
    // {
    //     return Booking::query()
    //         ->with(['designer', 'invoices'])
    //         ->whereIn('status', ['confirmed', 'completed'])
    //         ->orderByDesc('scheduled_at')
    //         ->get()
    //         ->map(function ($booking) {
    //             $hasDeposit = $booking->invoices->contains('type', 'deposit');
    //             $hasFinal = $booking->invoices->contains('type', 'final_balance');
    //
    //             if ($hasDeposit && $hasFinal) {
    //                 return null;
    //             }
    //
    //             $depositAmount = $hasDeposit
    //                 ? null
    //                 : ($booking->total_amount * ($booking->deposit_percentage ?? 30) / 100);
    //             $finalAmount = $hasDeposit
    //                 ? (float) $booking->total_amount - (float) ($booking->invoices->firstWhere('type', 'deposit')?->amount_due ?? 0)
    //                 : null;
    //
    //             return [
    //                 'id' => $booking->id,
    //                 'booking_number' => $booking->booking_number ?? $booking->id,
    //                 'customer_name' => $booking->customer?->customer?->full_name ?? $booking->customer?->name ?? '—',
    //                 'designer_name' => $booking->designer?->name ?? '—',
    //                 'scheduled_at' => $booking->scheduled_at?->format('d/m/Y'),
    //                 'total_amount' => $booking->total_amount,
    //                 'deposit_percentage' => $booking->deposit_percentage ?? 30,
    //                 'has_deposit' => $hasDeposit,
    //                 'has_final' => $hasFinal,
    //                 'deposit_amount' => $depositAmount,
    //                 'final_amount' => $finalAmount,
    //             ];
    //         })
    //         ->filter();
    // }
}
