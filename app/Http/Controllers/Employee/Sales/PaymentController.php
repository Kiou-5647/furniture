<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Sales\ProcessPaymentAction;
use App\Data\Sales\PaymentFilterData;
use App\Http\Requests\Sales\ProcessPaymentRequest;
use App\Http\Resources\Employee\Sales\PaymentResource;
use App\Services\Sales\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController
{
    public function __construct(
        private PaymentService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = PaymentFilterData::fromRequest($request);

        return Inertia::render('employee/sales/payments/Index', [
            'gatewayOptions' => $this->service->getGatewayOptions(),
            'customerOptions' => $this->service->getCustomerOptions(),
            'payments' => Inertia::defer(fn () => PaymentResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(ProcessPaymentRequest $request, ProcessPaymentAction $action)
    {
        $payment = $action->execute($request->validated());

        return back()->with('success', 'Đã ghi nhận thanh toán.');
    }
}
