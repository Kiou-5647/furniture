<?php

namespace App\Http\Controllers\Payment;

use App\Actions\Payment\ProcessVnPayReturnAction;
use App\Enums\UserType;
use App\Models\Booking\Booking;
use App\Models\Sales\Invoice;
use App\Models\Sales\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $redirectUrl = '';
            $user = Auth::user();
            if ($user->type === UserType::Customer) {
                $redirectUrl = $this->buildCustomerRedirectUrl($invoice);
            } else if ($user->type === UserType::Employee) {
                $redirectUrl = $this->buildEmployeeRedirectUrl($invoice);
            } else {
                $redirectUrl = route('home');
            }


            return redirect($redirectUrl)->with('success', $result['message']);
        }

        // On failure, redirect back to a generic payment status page
        return redirect()->route('payment.vnpay.status')
            ->with('error', $result['message'])
            ->with('vnp_response', $returnData);
    }

    protected function buildCustomerRedirectUrl(?Invoice $invoice): string
    {
        if (! $invoice) {
            return route('home');
        }

        $invoiceable = $invoice->invoiceable;

        if ($invoiceable instanceof Order) {
            return route('customer.profile.orders.show', $invoiceable->order_number);
        }

        if ($invoiceable instanceof Booking) {
            return route('customer.profile.bookings.show', $invoiceable->booking_number);
        }

        return route('home');
    }

    protected function buildEmployeeRedirectUrl(?Invoice $invoice): string
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
