<?php

namespace App\Services\Booking;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Booking\Booking;
use App\Models\Sales\Invoice;

class BookingInvoiceService
{
    public function createDepositInvoice(Booking $booking): Invoice
    {
        $service = $booking->service;
        $depositAmount = (float) $service->base_price * ($service->deposit_percentage / 100);

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoiceable_type' => Booking::class,
            'invoiceable_id' => $booking->id,
            'type' => InvoiceType::Deposit,
            'status' => InvoiceStatus::Open,
            'amount_due' => $depositAmount,
            'amount_paid' => 0,
        ]);

        $booking->update(['deposit_invoice_id' => $invoice->id]);

        return $invoice;
    }

    public function createFinalInvoice(Booking $booking): Invoice
    {
        $service = $booking->service;
        $depositAmount = $booking->depositInvoice
            ? (float) $booking->depositInvoice->amount_due
            : 0;
        $finalAmount = (float) $service->base_price - $depositAmount;

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoiceable_type' => Booking::class,
            'invoiceable_id' => $booking->id,
            'type' => InvoiceType::FinalBalance,
            'status' => InvoiceStatus::Draft,
            'amount_due' => $finalAmount,
            'amount_paid' => 0,
        ]);

        $booking->update(['final_invoice_id' => $invoice->id]);

        return $invoice;
    }

    public function markDepositPaid(Booking $booking): Invoice
    {
        $invoice = $booking->depositInvoice;

        if (! $invoice) {
            throw new \RuntimeException('Deposit invoice not found');
        }

        if ($invoice->status !== InvoiceStatus::Open) {
            throw new \RuntimeException('Deposit invoice is not in pending status');
        }

        $invoice->update([
            'status' => InvoiceStatus::Paid,
            'amount_paid' => $invoice->amount_due,
        ]);

        return $invoice;
    }

    public function getDepositAmount(Booking $booking): float
    {
        $service = $booking->service;

        return (float) $service->base_price * ($service->deposit_percentage / 100);
    }

    public function getFinalAmount(Booking $booking): float
    {
        $service = $booking->service;
        $depositAmount = $this->getDepositAmount($booking);

        return max(0, (float) $service->base_price - $depositAmount);
    }
}
