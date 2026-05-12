<?php

namespace App\Actions\Sales;

use App\Data\Sales\CreateInvoiceData;
use App\Models\Booking\Booking;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class CreateInvoiceAction
{
    public function execute(CreateInvoiceData $data): Invoice
    {
        DB::beginTransaction();

        try {
            $this->resolveInvoiceable($data->invoiceable_type, $data->invoiceable_id);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoiceable_type' => $data->invoiceable_type,
                'invoiceable_id' => $data->invoiceable_id,
                'type' => $data->type,
                'amount_due' => $data->amount_due,
                'amount_paid' => 0,
                'status' => 'open',
                'validated_by' => $data->validated_by,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();

        return $invoice->fresh();
    }

    protected function resolveInvoiceable(string $type, string $id): Order|Booking
    {
        return match ($type) {
            Order::class => Order::findOrFail($id),
            Booking::class => Booking::findOrFail($id),
            default => throw new \InvalidArgumentException('Loại hóa đơn không hợp lệ'),
        };
    }
}
