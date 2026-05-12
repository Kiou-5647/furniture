<?php

namespace App\Http\Controllers\Employee\Sales;

use App\Actions\Sales\CreateInvoiceAction;
use App\Data\Sales\CreateInvoiceData;
use App\Data\Sales\InvoiceFilterData;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Http\Requests\Sales\CreateInvoiceRequest;
use App\Http\Resources\Employee\Sales\InvoiceResource;
use App\Models\Booking\Booking;
use App\Models\Sales\Invoice;
use App\Services\Sales\InvoiceService;
use App\Settings\BookingSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class InvoiceController
{
    public function __construct(
        private InvoiceService $service,
        private BookingSettings $settings,
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Invoice::class)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $filter = InvoiceFilterData::fromRequest($request);

        return Inertia::render('employee/sales/invoices/Index', [
            'canCreate' => Gate::allows('create', Invoice::class),
            'invoices' => Inertia::defer(fn() => InvoiceResource::collection(
                $this->service->getFiltered($filter, $request->user())
            )),
            'statusOptions' => $this->service->getStatusOptions(),
            'typeOptions' => $this->service->getTypeOptions(),
            'currentEmployeeId' => Auth::user()?->employee?->id,
            'orderOptions' => $this->service->getOrderOptions($request->user())->values(),
            'bookingOptions' => $this->service->getBookingOptions($request->user())->values(),
            'depositPercentage' => $this->settings->deposit_percentage,
            'filters' => $filter,
        ]);
    }

    public function show(Request $request, Invoice $invoice)
    {
        if (!Gate::allows('view', $invoice)) {
            return back()->with('error', 'Bạn không có quyền xem hóa đơn này!');
        }

        $invoice = $this->service->getById($invoice->id, $request->user());

        return Inertia::render('employee/sales/invoices/Show', [
            'invoice' => new InvoiceResource($invoice),
        ]);
    }

    public function store(CreateInvoiceRequest $request, CreateInvoiceAction $action)
    {
        if (!Gate::allows('create', Invoice::class)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $data = CreateInvoiceData::fromArray($request->validated());
        try {
            $invoice = $action->execute($data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('employee.sales.invoices.show', $invoice);
    }

    public function destroy(Invoice $invoice)
    {
        if (!Gate::allows('delete', $invoice)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        if ($invoice->invoiceable_type === Booking::class) {
            /** @var \App\Models\Booking\Booking $booking */
            $booking = $invoice->invoiceable;
            if ($invoice->type === InvoiceType::Deposit) {
                $booking->update(['deposit_invoice_id' => null]);
            } else {
                $booking->update(['final_invoice_id' => null]);
            }
        }

        $invoice->delete();

        return redirect(route('employee.sales.invoices.index'));
    }

    public function cancel(Invoice $invoice)
    {
        if (!Gate::allows('update', $invoice)) {
            return back()->with('error', 'Bạn không có quyền hủy hóa đơn này!');
        }

        $invoice->update(['status' => InvoiceStatus::Cancelled]);

        return back();
    }
}
