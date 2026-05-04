<?php

namespace App\Http\Controllers\Employee\Booking;

use App\Actions\Booking\CancelBookingAction;
use App\Actions\Booking\ConfirmBookingAction;
use App\Actions\Booking\CreateBookingAction;
use App\Actions\Booking\MarkBookingAsPaidAction;
use App\Data\Booking\BookingFilterData;
use App\Data\Booking\CreateBookingData;
use App\Enums\InvoiceStatus;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Resources\Employee\Booking\BookingResource;
use App\Models\Booking\Booking;
use App\Services\Booking\BookingService;
use App\Services\Hr\DesignerService;
use App\Settings\BookingSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BookingController
{
    public function __construct(
        private BookingService $service,
        private DesignerService $designerService,
        private BookingSettings $settings
    ) {}

    public function index(Request $request): Response
    {
        $filter = BookingFilterData::fromRequest($request);

        return Inertia::render('employee/booking/bookings/Index', [
            'deposit_percentage' => $this->settings->deposit_percentage,
            'statusOptions' => $this->service->getStatusOptions(),
            'customerOptions' => $this->service->getCustomerOptions(),
            'designerOptions' => $this->designerService->getActiveOptions()->toArray(),
            'bookings' => Inertia::defer(fn() => BookingResource::collection(
                $this->service->getFiltered($filter, $request->user())
            )),
            'filters' => $filter,
        ]);
    }

    public function store(CreateBookingRequest $request, CreateBookingAction $action)
    {
        Gate::authorize('create', Booking::class);

        $data = CreateBookingData::fromRequest($request);
        try {
            $booking = $action->execute($data);
        } catch (\Exception $e) {
            return back()->withErrors(['error', $e->getMessage()]);
        }


        return redirect()->route('employee.booking.show', $booking)
            ->with('success', 'Đã tạo đặt lịch.');
    }

    public function trash(Request $request): Response
    {
        $filter = BookingFilterData::fromRequest($request);

        return Inertia::render('employee/booking/bookings/Trash', [
            'bookings' => Inertia::defer(fn() => BookingResource::collection(
                $this->service->getTrashedFiltered($filter, $request->user())
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Booking $booking, Request $request): Response
    {
        $booking = $this->service->getById($booking->id, $request->user());

        return Inertia::render('employee/booking/bookings/Show', [
            'booking' => new BookingResource($booking),
        ]);
    }

    public function confirm(Booking $booking, Request $request, ConfirmBookingAction $action)
    {
        Gate::authorize('confirm', $booking);

        $action->execute($booking);

        return back()->with('success', 'Đã xác nhận đặt lịch.');
    }

    public function cancel(Booking $booking, Request $request, CancelBookingAction $action)
    {
        Gate::authorize('cancel', $booking);
        $employee = $request->user()->employee;

        $action->execute($booking, $employee);

        return back()->with('success', 'Đã hủy đặt lịch.');
    }

    public function destroy(Booking $booking)
    {
        Gate::authorize('manage', $booking);

        $booking->delete();

        return back()->with('success', 'Đã xóa đặt lịch.');
    }

    public function restore(Booking $booking)
    {
        Gate::authorize('manage', $booking);

        $booking->restore();

        return back()->with('success', 'Đã khôi phục đặt lịch.');
    }

    public function forceDestroy(Booking $booking)
    {
        Gate::authorize('manage', $booking);

        $booking->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn đặt lịch.');
    }

    public function openInvoice(Booking $booking)
    {
        Gate::authorize('openInvoice', $booking);

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
        $request->validate([
            'invoice_type' => ['required', 'string', 'in:deposit,final'],
            'gateway' => ['required', 'string'], // 'cash', 'vnpay', etc.
        ]);

        try {
            // We pass the current authenticated employee as the performer
            $employee = Auth::user()->employee;

            $action->execute(
                $booking,
                $request->invoice_type,
                $employee
            );

            return back()->with('success', 'Đã ghi nhận thanh toán thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
