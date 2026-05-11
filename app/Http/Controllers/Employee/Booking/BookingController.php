<?php

namespace App\Http\Controllers\Employee\Booking;

use App\Actions\Booking\CancelBookingAction;
use App\Actions\Booking\ConfirmBookingAction;
use App\Actions\Booking\CreateBookingAction;
use App\Actions\Booking\MarkBookingAsPaidAction;
use App\Data\Booking\BookingFilterData;
use App\Data\Booking\CreateBookingData;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Resources\Employee\Booking\BookingResource;
use App\Models\Booking\Booking;
use App\Services\Booking\BookingService;
use App\Services\Hr\DesignerService;
use App\Settings\BookingSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BookingController
{
    public function __construct(
        private BookingService $service,
        private DesignerService $designerService,
        private BookingSettings $settings
    ) {}

    public function index(Request $request)
    {
        if (!Gate::allows('viewAny', Booking::class)) {
            return back()->with('error', 'Bạn không có quyền truy cập danh sách đơn hàng!');
        }

        $filter = BookingFilterData::fromRequest($request);

        return Inertia::render('employee/booking/bookings/Index', [
            'bookings' => Inertia::defer(fn() => BookingResource::collection(
                $this->service->getFiltered($filter, $request->user())
            )),
            'deposit_percentage' => $this->settings->deposit_percentage,
            'statusOptions' => BookingStatus::options(),
            'designerOptions' => $this->designerService->getActiveOptions()->toArray(),
            'customerOptions' => $this->service->getCustomerOptions(),
            'filters' => $filter,
        ]);
    }

    public function show(Request $request, Booking $booking)
    {
        if (!Gate::allows('view', $booking)) {
            return back()->with('error', 'Bạn không có quyền xem chi tiết đặt lịch này!');
        }

        $booking = $this->service->getById($booking->id, $request->user());

        return Inertia::render('employee/booking/bookings/Show', [
            'booking' => new BookingResource($booking),
        ]);
    }

    public function store(CreateBookingRequest $request, CreateBookingAction $action)
    {
        if (!Gate::allows('create', Booking::class)) {
            return back()->with('error', 'Bạn không có quyền tạo đặt lịch!');
        }

        $data = CreateBookingData::fromRequest($request);
        try {
            $booking = $action->execute($data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }


        return redirect()->route('employee.booking.show', $booking)
            ->with('success', 'Đã tạo đặt lịch.');
    }

    public function confirm(Booking $booking, Request $request, ConfirmBookingAction $action)
    {
        if (!Gate::allows('confirm', $booking)) {
            return back()->with('error', 'Bạn không có quyền xác nhận đặt lịch này!');
        }

        try {
            $booking = $action->execute($booking);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã xác nhận đặt lịch.');
    }

    public function cancel(Booking $booking, Request $request, CancelBookingAction $action)
    {
        if (!Gate::allows('cancel', $booking)) {
            return back()->with('error', 'Bạn không có quyền hủy đặt lịch này!');
        }

        $employee = $request->user()->employee;

        try {
            $booking = $action->execute($booking, $employee);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã hủy đặt lịch.');
    }

    public function openInvoice(Booking $booking)
    {
        if (!Gate::allows('openInvoice', $booking)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $finalInvoice = $booking->finalInvoice;

        if (! $finalInvoice) {
            return back()->with('error', 'Hóa đơn cuối chưa được tạo.');
        }

        if ($finalInvoice->status->value !== 'draft') {
            return back()->with('error', 'Hóa đơn không ở trạng thái nháp.');
        }

        $finalInvoice->status = InvoiceStatus::Open;
        $finalInvoice->save();

        return back()->with('success', 'Đã mở hóa đơn thanh toán.');
    }

    public function markAsPaid(Request $request, Booking $booking, MarkBookingAsPaidAction $action)
    {
        if (!Gate::allows('markAsPaid', $booking)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $request->validate([
            'invoice_type' => ['required', 'string', 'in:deposit,final'],
            'gateway' => ['required', 'string'],
        ]);

        try {
            $employee = $request->user()->employee;

            $action->execute(
                $booking,
                $request->invoice_type,
                $employee
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã ghi nhận thanh toán thành công!');
    }

    public function destroy(Booking $booking)
    {
        if (!Gate::allows('delete', $booking)) {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này!');
        }

        $booking->delete();

        return back()->with('success', 'Đã xóa đặt lịch.');
    }
}
