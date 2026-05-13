<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Sales\ProcessPaymentAction;
use App\Data\Sales\PaymentFilterData;
use App\Http\Requests\Sales\ProcessPaymentRequest;
use App\Http\Resources\Employee\Sales\PaymentResource;
use App\Models\Sales\Payment;
use App\Services\Sales\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController
{
    public function __construct(
        private PaymentService $service,
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Payment::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập danh sách thanh toán.');
        }

        $filter = PaymentFilterData::fromRequest($request);

        return Inertia::render('employee/sales/payments/Index', [
            'gatewayOptions' => $this->service->getGatewayOptions(),
            'customerOptions' => $this->service->getCustomerOptions(),
            'payments' => Inertia::defer(fn() => PaymentResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(ProcessPaymentRequest $request, ProcessPaymentAction $action)
    {
        try {
            $action->execute($request->validated());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã ghi nhận thanh toán.');
    }
}
