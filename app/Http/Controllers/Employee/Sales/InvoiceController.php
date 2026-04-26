<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Sales\CreateInvoiceAction;
use App\Data\Sales\CreateInvoiceData;
use App\Data\Sales\InvoiceFilterData;
use App\Http\Requests\Sales\CreateInvoiceRequest;
use App\Http\Resources\Employee\Sales\InvoiceResource;
use App\Models\Sales\Invoice;
use App\Services\Sales\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController
{
    public function __construct(
        private InvoiceService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = InvoiceFilterData::fromRequest($request);

        return Inertia::render('employee/sales/invoices/Index', [
            'statusOptions' => $this->service->getStatusOptions(),
            'typeOptions' => $this->service->getTypeOptions(),
            'currentEmployeeId' => Auth::user()?->employee?->id,
            'orderOptions' => $this->service->getOrderOptions()->values(),
            // 'bookingOptions' => $this->service->getBookingOptions()->values(),
            'invoices' => Inertia::defer(fn() => InvoiceResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = InvoiceFilterData::fromRequest($request);

        return Inertia::render('employee/sales/invoices/Trash', [
            'invoices' => Inertia::defer(fn() => InvoiceResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $invoice = $this->service->getById($invoice->id);

        return Inertia::render('employee/sales/invoices/Show', [
            'invoice' => new InvoiceResource($invoice),
        ]);
    }

    public function store(CreateInvoiceRequest $request, CreateInvoiceAction $action)
    {
        Gate::authorize('create', Invoice::class);

        $data = CreateInvoiceData::fromArray($request->validated());
        $invoice = $action->execute($data);

        return redirect()->route('employee.sales.invoices.show', $invoice)
            ->with('success', 'Đã tạo hóa đơn mới.');
    }

    public function destroy(Invoice $invoice)
    {
        Gate::authorize('manage', $invoice);

        $invoice->delete();

        return back()->with('success', 'Đã xóa hóa đơn.');
    }

    public function restore(Invoice $invoice)
    {
        Gate::authorize('manage', $invoice);

        $invoice->restore();

        return back()->with('success', 'Đã khôi phục hóa đơn.');
    }

    public function forceDestroy(Invoice $invoice)
    {
        Gate::authorize('manage', $invoice);

        $invoice->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn hóa đơn.');
    }
}
