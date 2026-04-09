<?php

namespace App\Actions\Sales;

use App\Data\Sales\CreateInvoiceData;
use App\Models\Design\Booking;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class CreateInvoiceAction
{
    public function execute(CreateInvoiceData $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $invoiceable = $this->resolveInvoiceable($data->invoiceable_type, $data->invoiceable_id);

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoiceable_type' => $data->invoiceable_type,
                'invoiceable_id' => $data->invoiceable_id,
                'type' => $data->type,
                'amount_due' => $data->amount_due,
                'amount_paid' => 0,
                'status' => 'open',
                'validated_by' => $data->validated_by,
            ]);

            return $invoice->fresh();
        });
    }

    protected function resolveInvoiceable(string $type, string $id): Order|Booking
    {
        return match ($type) {
            Order::class => Order::findOrFail($id),
            Booking::class => Booking::findOrFail($id),
            default => throw new \InvalidArgumentException('Invalid invoiceable type.'),
        };
    }

    protected function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastInvoice = Invoice::whereDate('created_at', today())
            ->orderBy('invoice_number', 'desc')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return 'INV-'.$date.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
