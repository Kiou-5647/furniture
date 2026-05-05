<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Booking\CancelBookingAction;
use App\Actions\Booking\CreateBookingAction;
use App\Data\Booking\BookingFilterData;
use App\Data\Booking\CreateBookingData;
use App\Enums\BookingStatus;
use App\Enums\UserType;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Resources\Public\Booking\BookingDetailsResource;
use App\Http\Resources\Public\Booking\BookingResource;
use App\Models\Booking\Booking;
use App\Models\Hr\Designer;
use App\Services\Booking\BookingAvailabilityService;
use App\Services\Booking\BookingService;
use App\Services\Hr\DesignerService;
use App\Settings\BookingSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BookingController
{
    public function __construct(
        private CreateBookingAction $createBookingAction,
        private DesignerService $designerService,
        private BookingAvailabilityService $availabilityService,
        private BookingService $bookingService,
        private BookingSettings $settings
    ) {}

    /**
     * Render the booking page.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        if (! $user || $user->type !== UserType::Customer) {
            abort(403, 'Vui lòng đăng nhập để sử dụng tính năng này!');
        }

        $customer = $user->customer;

        if (! $customer) {
            abort(403, 'Không tìm thấy thông tin cá nhân!');
        }

        $designers = Designer::query()
            ->where('is_active', true)
            ->with(['user'])
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'full_name' => $d->full_name,
                'bio' => $d->bio,
                'hourly_rate' => $d->hourly_rate,
                'avatar_url' => $d->getFirstMediaUrl('avatar'),
                'phone' => $d->phone,
                'portfolio_url' => $d->portfolio_url,
            ]);

        return Inertia::render('public/bookings/Index', [
            'deposit_percentage' => $this->settings->deposit_percentage,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->user->name,
                'email' => $customer->user->email,
                'phone' => $customer->phone,
                'province_code' => $customer->province_code,
                'province_name' => $customer->province_name,
                'ward_code' => $customer->ward_code,
                'ward_name' => $customer->ward_name,
                'street' => $customer->street ?? null,
            ],
            'designers' => $designers,
        ]);
    }

    public function profileIndex(Request $request): Response
    {
        $user = Auth::user();

        $filter = new BookingFilterData(
            customer_id: $user->id,
            status: $request->query('status') ? BookingStatus::tryFrom($request->query('status')) : BookingStatus::PendingDeposit,
            search: $request->query('search'),
            order_by: $request->query('order_by', 'created_at'),
            order_direction: $request->query('order_direction', 'desc'),
            per_page: (int) ($request->query('per_page') ?? 15),
        );

        $bookings = $this->bookingService->getFiltered($filter, $request->user());

        return Inertia::render('public/bookings/ProfileIndex', [
            'bookings' => BookingResource::collection($bookings),
            'filters' => $request->all(),
            'deposit_percentage' => $this->settings->deposit_percentage,
        ]);
    }

    public function show(string $booking_number): Response
    {
        $booking = Booking::with(['designer', 'depositInvoice', 'finalInvoice'])->where('booking_number', $booking_number)
            ->firstOrFail();

        if ($booking->customer_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('public/bookings/Show', [
            'booking' => new BookingDetailsResource($booking),
        ]);
    }

    public function cancel(string $booking_number, CancelBookingAction $action): RedirectResponse
    {
        $booking = Booking::where('booking_number', $booking_number)->firstOrFail();

        if ($booking->customer_id !== Auth::id()) {
            abort(403);
        }

        try {
            $action->execute($booking, null);

            return back()->with('success', 'Lịch đặt đã được hủy thành công.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new booking.
     */
    public function store(CreateBookingRequest $request): RedirectResponse
    {
        $data = CreateBookingData::fromRequest($request);
        try {
            $this->createBookingAction->execute($data);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('customer.profile.bookings')
            ->with('success', 'Đặt lịch tư vấn thành công! Chúng tôi sẽ liên hệ với bạn sớm.');
    }

    /**
     * API: Get weekly availability grid.
     */
    public function availabilities(Designer $designer): JsonResponse
    {
        return response()->json([
            'weekly' => $this->designerService->getWeeklySlots($designer),
        ]);
    }

    /**
     * API: Get actual available slots for a specific date.
     */
    public function availableSlots(Request $request, Designer $designer): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $availableSlots = $this->availabilityService->getAvailableSlotsForDate(
            $designer,
            $request->input('date')
        );

        $slotsMap = [];
        for ($hour = 5; $hour <= 23; $hour++) {
            $slotsMap[$hour] = in_array($hour, $availableSlots) ? 1 : 0;
        }

        return response()->json([
            'date' => $request->input('date'),
            'slots' => $slotsMap,
        ]);
    }
}
