<?php

namespace App\Http\Controllers\Employee\Finance;

use App\Actions\Finance\CreateInvoiceAction;
use App\Data\Finance\CreateInvoiceData;
use App\Data\Finance\InvoiceFilterData;
use App\Http\Requests\Finance\CreateInvoiceRequest;
use App\Http\Resources\Employee\Finance\InvoiceResource;
use App\Models\Finance\Invoice;
use App\Services\Finance\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return Inertia::render('employee/finance/invoices/Index', [
            'statusOptions' => $this->service->getStatusOptions(),
            'typeOptions' => $this->service->getTypeOptions(),
            'invoices' => Inertia::defer(fn () => InvoiceResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function trash(Request $request): Response
    {
        $filter = InvoiceFilterData::fromRequest($request);

        return Inertia::render('employee/finance/invoices/Trash', [
            'invoices' => Inertia::defer(fn () => InvoiceResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $invoice = $this->service->getById($invoice->id);

        return Inertia::render('employee/finance/invoices/Show', [
            'invoice' => new InvoiceResource($invoice),
        ]);
    }

    public function store(CreateInvoiceRequest $request, CreateInvoiceAction $action)
    {
        $data = CreateInvoiceData::fromArray($request->validated());
        $invoice = $action->execute($data);

        return redirect()->route('employee.finance.invoices.show', $invoice)
            ->with('success', 'Đã tạo hóa đơn mới.');
    }

    public function destroy(Invoice $invoice)
    {
        if (! Auth::user()->can('invoices.delete')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $invoice->delete();

        return back()->with('success', 'Đã xóa hóa đơn.');
    }

    public function restore(Invoice $invoice)
    {
        if (! Auth::user()->can('invoices.manage')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $invoice->restore();

        return back()->with('success', 'Đã khôi phục hóa đơn.');
    }

    public function forceDestroy(Invoice $invoice)
    {
        if (! Auth::user()->can('invoices.force_delete')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa vĩnh viễn!');
        }

        $invoice->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn hóa đơn.');
    }
}
