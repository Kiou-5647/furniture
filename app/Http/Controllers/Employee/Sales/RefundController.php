<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Sales\ProcessRefundAction;
use App\Data\Sales\RefundFilterData;
use App\Http\Resources\Employee\Sales\RefundResource;
use App\Models\Sales\Refund;
use App\Services\Sales\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RefundController
{
    public function __construct(
        private RefundService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = RefundFilterData::fromRequest($request);

        return Inertia::render('employee/sales/refunds/Index', [
            'statusOptions' => $this->service->getStatusOptions(),
            'refunds' => Inertia::defer(fn() => RefundResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Refund $refund): Response
    {
        $refund->load([
            'order.customer',
            'booking.customer',
            'invoice',
            'requestedBy',
            'processedBy'
        ]);

        return Inertia::render('employee/sales/refunds/Show', [
            'refund' => new RefundResource($refund),
        ]);
    }

    public function approve(Request $request, Refund $refund, ProcessRefundAction $action)
    {
        Gate::authorize('approve', $refund);
        $employee = $request->user()->employee;

        try {
            $action->approve($refund, $employee);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã duyệt yêu cầu hoàn tiền.');
    }

    public function reject(Request $request, Refund $refund, ProcessRefundAction $action)
    {
        Gate::authorize('reject', $refund);
        $employee = $request->user()->employee;
        $notes = $request->input('notes', '');

        try {
            $action->reject($refund, $employee, $notes);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã từ chối yêu cầu hoàn tiền.');
    }
}
