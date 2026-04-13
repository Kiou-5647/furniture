<?php

namespace App\Http\Controllers\Customer\Booking;

use App\Actions\Booking\CreateBookingAction;
use App\Data\Booking\CreateBookingData;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Resources\Public\Booking\BookingResource;
use App\Models\Booking\Booking;
use App\Models\Booking\DesignService;
use App\Models\Hr\Designer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController
{
    public function index(Request $request): Response
    {
        $services = DesignService::whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'base_price', 'is_schedule_blocking', 'estimated_hours', 'deposit_percentage']);

        $designers = Designer::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'auto_confirm_bookings']);

        return Inertia::render('customer/booking/Book', [
            'services' => $services,
            'designers' => $designers,
        ]);
    }

    public function store(CreateBookingRequest $request, CreateBookingAction $action)
    {
        $data = CreateBookingData::fromRequest($request);

        try {
            $booking = $action->execute($data);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('customer.booking.show', $booking)
            ->with('success', 'Đã tạo đặt lịch. Vui lòng thanh toán đặt cọc.');
    }

    public function history(Request $request): Response
    {
        $customerId = $request->user()->id;

        $bookings = Booking::with(['designer', 'service', 'depositInvoice', 'finalInvoice'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('customer/booking/History', [
            'bookings' => BookingResource::collection($bookings),
        ]);
    }

    public function show(Booking $booking): Response
    {
        $booking->load(['designer', 'service', 'sessions', 'depositInvoice', 'finalInvoice']);

        return Inertia::render('customer/booking/Show', [
            'booking' => new BookingResource($booking),
        ]);
    }

    // TODO: Implement later
    //public function payDeposit(Booking $booking, Request $request): RedirectResponse
    //{

    //}
}
