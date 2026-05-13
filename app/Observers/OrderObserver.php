<?php

namespace App\Observers;

use App\Actions\Customer\UpdateCustomerTotalSpentAction;
use App\Actions\Sales\CreateRefundRequestAction;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Enums\ShipmentStatus;
use App\Models\Hr\Employee;
use App\Models\Product\Bundle;
use App\Models\Product\BundleContent;
use App\Models\Product\ProductVariant;
use App\Models\Sales\Order;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     *
     * When an order transitions to completed AND its invoice is overpaid
     * (due to returned items reducing amount_due below amount_paid),
     * auto-create a refund request (Pending) for the excess amount.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') && $order->status === OrderStatus::Completed) {
            app(UpdateCustomerTotalSpentAction::class)->execute($order);

            $hasDeliveredItems = $order->shipments()
                ->whereHas('items', function ($q) {
                    $q->where('status', ShipmentStatus::Delivered);
                })->exists();

            if (!$hasDeliveredItems) {
                foreach ($order->items as $item) {
                    $quantity = $item->quantity;

                    if ($item->purchasable_type === Bundle::class) {
                        $configuration = $item->configuration ?? [];
                        foreach ($configuration as $configValue) {
                            if (is_array($configValue) && isset($configValue['variant_id'])) {
                                $variantId = $configValue['variant_id'];
                                $totalIncrement = $quantity * ($configValue['quantity'] ?? 1);

                                $this->incrementVariantSales($variantId, $totalIncrement);
                            }
                        }
                    } else {
                        $this->incrementVariantSales($item->purchasable_id, $quantity);
                    }
                }
            }

            $this->createRefundForOverpaidInvoice($order);
        }
    }

    protected function incrementVariantSales(string $variantId, int $quantity): void
    {
        $variant = ProductVariant::find($variantId);
        if ($variant) {
            $variant->increment('sales_count', $quantity);
            $variant->productCard?->syncSalesCount();
            $variant->product?->syncSalesCount();
        }
    }

    protected function createRefundForOverpaidInvoice(Order $order): void
    {
        $invoice = $order->invoices()->first();
        if (! $invoice || $invoice->amount_paid <= $invoice->amount_due) {
            return;
        }

        // Cập nhật hóa đơn thành thanh toán vượt mức
        if ($invoice->status !== InvoiceStatus::Overpaid) {
            $invoice->updateQuietly(['status' => InvoiceStatus::Overpaid]);
        }

        $overpaidAmount = $invoice->amount_paid - $invoice->amount_due;

        // Kiểm tra xem có đơn hoàn tiền nào đang chờ không
        $existingRefund = $order->refunds()
            ->where('status', RefundStatus::Pending)
            ->first();

        // Nếu có đơn hoàn tiền đang chờ thì update số lượng cần hoàn
        if ($existingRefund) {
            $existingRefund->update([
                'amount' => max(0, (float) $overpaidAmount),
            ]);

            return;
        }

        // Lấy nhân viên xử lý đơn hàng
        $employee = $order->acceptedBy
            ?? Employee::whereHas('user', fn($q) => $q->where('type', 'employee'))->first();

        if ($employee) {
            app(CreateRefundRequestAction::class)->execute(
                $invoice,
                $employee,
                'Hoàn tiền thừa (đơn hàng hoàn thành, thanh toán vượt mức)'
            );
        }
    }
}
