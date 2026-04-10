<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Sales\ProcessPaymentAction;
use App\Actions\Sales\RefundPaymentAction;
use App\Data\Sales\PaymentFilterData;
use App\Http\Requests\Sales\ProcessPaymentRequest;
use App\Http\Resources\Employee\Sales\PaymentResource;
use App\Models\Sales\Payment;
use App\Services\Sales\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'statusOptions' => $this->service->getStatusOptions(),
            'gatewayOptions' => $this->service->getGatewayOptions(),
            'payments' => Inertia::defer(fn () => PaymentResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Payment $payment): Response
    {
        $payment = $this->service->getById($payment->id);

        return Inertia::render('employee/sales/payments/Show', [
            'payment' => new PaymentResource($payment),
        ]);
    }

    public function store(ProcessPaymentRequest $request, ProcessPaymentAction $action)
    {
        $payment = $action->execute($request->validated());

        return redirect()->route('employee.sales.payments.show', $payment)
            ->with('success', 'Đã ghi nhận thanh toán.');
    }

    public function refund(Payment $payment, Request $request, RefundPaymentAction $action)
    {
        $this->authorize('update', $payment);

        $employee = $request->user()->employee;

        $action->execute($payment, $employee);

        return back()->with('success', 'Đã hoàn tiền thanh toán.');
    }

    public function destroy(Payment $payment)
    {
        if (! Auth::user()->can('payments.delete')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $payment->delete();

        return back()->with('success', 'Đã xóa thanh toán.');
    }

    public function forceDestroy(Payment $payment)
    {
        if (! Auth::user()->can('payments.force_delete')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa vĩnh viễn!');
        }

        $payment->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn thanh toán.');
    }
}
