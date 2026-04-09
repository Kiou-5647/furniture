<?php

namespace App\Http\Controllers\Employee\Booking;

use App\Actions\Booking\CancelBookingAction;
use App\Actions\Booking\ConfirmBookingAction;
use App\Actions\Booking\CreateBookingAction;
use App\Data\Booking\BookingFilterData;
use App\Data\Booking\CreateBookingData;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Resources\Employee\Booking\BookingResource;
use App\Models\Booking\Booking;
use App\Services\Booking\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BookingController
{
    public function __construct(
        private BookingService $service,
    ) {}

    public function index(Request $request): Response
    {
        $filter = BookingFilterData::fromRequest($request);

        return Inertia::render('employee/booking/bookings/Index', [
            'statusOptions' => $this->service->getStatusOptions(),
            'bookings' => Inertia::defer(fn () => BookingResource::collection(
                $this->service->getFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function store(CreateBookingRequest $request, CreateBookingAction $action)
    {
        $data = CreateBookingData::fromRequest($request);
        $booking = $action->execute($data);

        return redirect()->route('employee.booking.show', $booking)
            ->with('success', 'Đã tạo đặt lịch.');
    }

    public function trash(Request $request): Response
    {
        $filter = BookingFilterData::fromRequest($request);

        return Inertia::render('employee/booking/bookings/Trash', [
            'bookings' => Inertia::defer(fn () => BookingResource::collection(
                $this->service->getTrashedFiltered($filter)
            )),
            'filters' => $filter,
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking = $this->service->getById($booking->id);

        return Inertia::render('employee/booking/bookings/Show', [
            'booking' => new BookingResource($booking),
        ]);
    }

    public function confirm(Booking $booking, Request $request, ConfirmBookingAction $action)
    {
        $employee = $request->user()->employee;

        $action->execute($booking, $employee);

        return back()->with('success', 'Đã xác nhận đặt lịch.');
    }

    public function cancel(Booking $booking, Request $request, CancelBookingAction $action)
    {
        $employee = $request->user()->employee;

        $action->execute($booking, $employee);

        return back()->with('success', 'Đã hủy đặt lịch.');
    }

    public function destroy(Booking $booking)
    {
        if (! Auth::user()->can('bookings.delete')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $booking->delete();

        return back()->with('success', 'Đã xóa đặt lịch.');
    }

    public function restore(Booking $booking)
    {
        if (! Auth::user()->can('bookings.restore')) {
            return back()->with('error', 'Không đủ quyền hạn!');
        }

        $booking->restore();

        return back()->with('success', 'Đã khôi phục đặt lịch.');
    }

    public function forceDestroy(Booking $booking)
    {
        if (! Auth::user()->can('bookings.force_delete')) {
            return back()->with('error', 'Không đủ quyền hạn để xóa vĩnh viễn!');
        }

        $booking->forceDelete();

        return back()->with('success', 'Đã xóa vĩnh viễn đặt lịch.');
    }
}
