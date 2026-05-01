<?php

namespace App\Http\Controllers\Payment;

use App\Actions\Payment\InitiateVnPayPaymentAction;
use App\Enums\UserType;
use App\Models\Booking\Booking;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VnPayPaymentController
{
    /**
     * Initiate VNPay payment for an invoice.
     * Redirects user to VNPay payment gateway.
     */
    public function initiate(Request $request, Invoice $invoice, InitiateVnPayPaymentAction $action): RedirectResponse
    {
        $this->authorizeInvoice($request, $invoice);

        $result = $action->execute($invoice);

        return redirect($result['payment_url']);
    }

    protected function authorizeInvoice(Request $request, Invoice $invoice): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Không đủ quyền hạn!');
        }

        $invoiceable = $invoice->invoiceable;

        // For orders: check orders.manage permission
        if ($invoiceable instanceof Order) {
            if ($user->type !== UserType::Customer && ! $user->can('Quản lý đơn hàng')) {
                abort(403, 'Không đủ quyền hạn tạo đơn hàng!');
            }
        }

        // For bookings: check bookings.manage permission
        if ($invoiceable instanceof Booking) {
            if ($user->type !== UserType::Customer && ! $user->can('Quản lý lịch thiết kế')) {
                abort(403, 'Không đủ quyền hạn tạo lịch thiết kế!');
            }
        }
    }
}
