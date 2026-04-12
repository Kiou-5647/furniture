<?php

namespace App\Actions\Fulfillment;

use App\Actions\Sales\CreateRefundRequestAction;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Enums\ShipmentStatus;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\ShipmentItem;
use App\Models\Sales\Refund;
use App\Services\Location\OrderStockDeductionService;
use Illuminate\Support\Facades\DB;

class ReturnShipmentItemAction
{
    public function __construct(
        protected OrderStockDeductionService $stockDeductionService,
    ) {}

    public function execute(ShipmentItem $shipmentItem, string $reason, ?Employee $performedBy = null): ShipmentItem
    {
        if (! in_array($shipmentItem->status, [ShipmentStatus::Shipped, ShipmentStatus::Delivered], true)) {
            throw new \RuntimeException('Chỉ có thể trả sản phẩm đã gửi hoặc đã giao.');
        }

        DB::transaction(function () use ($shipmentItem, $performedBy, $reason) {
            $this->stockDeductionService->restoreStockForShipmentItem($shipmentItem, $performedBy, $reason);

            $shipmentItem->update([
                'status' => ShipmentStatus::Returned,
            ]);

            $this->checkShipmentStatus($shipmentItem);

            $this->handleReturnRefund($shipmentItem, $performedBy, $reason);
        });

        return $shipmentItem->refresh();
    }

    /**
     * When an item is returned on a paid order, calculate the remaining
     * order value and create/update a refund request for the difference.
     */
    protected function handleReturnRefund(ShipmentItem $returnedItem, ?Employee $performedBy, string $reason): void
    {
        $order = $returnedItem->shipment->order;

        // Only process refunds for completed/paid orders
        if (! $order || $order->status !== OrderStatus::Completed) {
            return;
        }

        $invoice = $order->invoices()->first();
        if (! $invoice || $invoice->amount_paid <= $invoice->amount_due) {
            return;
        }

        $overpaidAmount = $invoice->amount_paid - $invoice->amount_due;

        // Update invoice status to overpaid if needed
        if ($invoice->status !== InvoiceStatus::Overpaid) {
            $invoice->updateQuietly(['status' => InvoiceStatus::Overpaid]);
        }

        // Check for existing pending refund for this order
        $existingRefund = $order->refunds()
            ->whereIn('status', [RefundStatus::Pending, RefundStatus::Processing])
            ->first();

        if ($existingRefund) {
            // Update the existing pending refund with the new amount
            $newAmount = max(0, (float) $overpaidAmount);
            $existingRefund->update([
                'amount' => $newAmount,
                'reason' => $existingRefund->reason
                    ? "{$existingRefund->reason}\n• {$reason}"
                    : $reason,
            ]);
        } else {
            $employee = $performedBy
                ?? $order->acceptedBy
                ?? Employee::whereHas('user', fn ($q) => $q->where('type', 'employee'))->first();

            if ($employee && $overpaidAmount > 0) {
                app(CreateRefundRequestAction::class)->execute($invoice, $employee, $reason);
            }
        }
    }

    protected function checkShipmentStatus(ShipmentItem $returnedItem): void
    {
        $shipment = $returnedItem->shipment;

        $allReturned = $shipment->items()->where('status', '!=', ShipmentStatus::Returned)->exists() === false;
        $allDelivered = $shipment->items()->whereNotIn('status', [ShipmentStatus::Delivered, ShipmentStatus::Returned])->exists() === false;

        if ($allReturned) {
            $shipment->update(['status' => ShipmentStatus::Returned]);
        } elseif ($allDelivered) {
            $shipment->update(['status' => ShipmentStatus::Delivered]);
        }
    }
}
