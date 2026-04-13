<?php

namespace App\Http\Controllers\Payment;

use App\Actions\Payment\ProcessVnPayReturnAction;
use App\Models\Booking\Booking;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VnPayReturnController
{
    public function __invoke(Request $request, ProcessVnPayReturnAction $action): RedirectResponse
    {
        $returnData = $request->all();

        Log::info('VNPay return received', ['data' => $returnData]);

        $result = $action->execute($returnData);

        if ($result['success']) {
            $invoice = $result['invoice'] ?? null;
            $redirectUrl = $this->buildRedirectUrl($invoice);

            return redirect($redirectUrl)->with('success', $result['message']);
        }

        // On failure, redirect back to a generic payment status page
        return redirect()->route('payment.vnpay.status')
            ->with('error', $result['message'])
            ->with('vnp_response', $returnData);
    }

    protected function buildRedirectUrl(?Invoice $invoice): string
    {
        if (! $invoice) {
            return route('employee.dashboard');
        }

        $invoiceable = $invoice->invoiceable;

        if ($invoiceable instanceof Order) {
            return route('employee.sales.orders.show', $invoiceable);
        }

        if ($invoiceable instanceof Booking) {
            return route('employee.booking.show', $invoiceable);
        }

        return route('employee.dashboard');
    }
}
