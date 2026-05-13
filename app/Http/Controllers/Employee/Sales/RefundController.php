<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Sales\ProcessRefundAction;
use App\Data\Sales\RefundFilterData;
use App\Enums\RefundStatus;
use App\Http\Resources\Employee\Sales\RefundResource;
use App\Models\Sales\Refund;
use App\Services\Sales\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RefundController
{
    public function __construct(
        private RefundService $service,
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Refund::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        $filter = RefundFilterData::fromRequest($request);

        return Inertia::render('employee/sales/refunds/Index', [
            'statusOptions' => RefundStatus::options(),
            'refunds' => Inertia::defer(fn() => RefundResource::collection(
                $this->service->getFiltered($filter, $request->user())
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Request $request, Refund $refund)
    {
        if (!Gate::allows('view', $refund)) {
            return back()->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        $refund = $this->service->getById($refund->id, $request->user());

        return Inertia::render('employee/sales/refunds/Show', [
            'refund' => new RefundResource($refund),
        ]);
    }

    public function approve(Request $request, Refund $refund, ProcessRefundAction $action)
    {
        if (!Gate::allows('approve', $refund)) {
            return back()->with('error', 'Bạn không có quyền chấp nhận đơn hoàn tiền này.');
        }

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
        if (!Gate::allows('reject', $refund)) {
            return back()->with('error', 'Bạn không có quyền từ chối đơn hoàn tiền này.');
        }

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
