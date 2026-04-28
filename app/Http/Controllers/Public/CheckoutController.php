<?php

namespace App\Http\Controllers\Public;

use App\Actions\Sales\CreateOrderAction;
use App\Data\Sales\CreateOrderData;
use App\Http\Requests\Sales\CreateOrderRequest;
use App\Models\Customer\Customer;
use App\Models\Fulfillment\ShippingMethod;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController
{
    public function __construct(
        protected CreateOrderAction $createOrder,
        protected GeneralSettings $settings,
    ) {}

    /**
     * Display the checkout page with pre-filled customer data and shipping methods.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $customer = Customer::where('user_id', $user->id)->with('user')->first();

        return Inertia::render('public/checkout/Index', [
            'customer' => $customer,
            'shipping_methods' => ShippingMethod::where('is_active', true)->get(),
            'freeship_threshold' => $this->settings->freeship_threshold,
        ]);
    }

    /**
     * Handle the order creation.
     */
    public function store(CreateOrderRequest $request)
    {
        try {
            $data = CreateOrderData::fromRequest($request);
            $order = $this->createOrder->execute($data);

            $invoice = \App\Models\Sales\Invoice::where('invoiceable_id', $order->id)
                ->where('invoiceable_type', \App\Models\Sales\Order::class)
                ->firstOrFail();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }


        if ($request->payment_method === 'bank_transfer') {
            return Inertia::location(route('payment.vnpay.initiate', ['invoice' => $invoice->id]));
        }

        return redirect()->route('customer.checkout.success', ['order' => $order->id])
            ->with('success', 'Đơn hàng của bạn đã được tạo thành công!');
    }

    /**
     * Display the order success page.
     */
    public function success(string $order): Response
    {
        return Inertia::render('public/checkout/Success', [
            'order_number' => $order,
        ]);
    }
}
