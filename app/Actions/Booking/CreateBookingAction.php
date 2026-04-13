<?php

namespace App\Actions\Booking;

use App\Data\Booking\CreateBookingData;
use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use App\Models\Booking\BookingSession;
use App\Models\Booking\DesignService;
use App\Models\Hr\Designer;
use App\Services\Booking\BookingAvailabilityChecker;
use App\Services\Booking\BookingInvoiceService;
use App\Services\Booking\DesignerAvailabilityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateBookingAction
{
    public function __construct(
        private DesignerAvailabilityService $availabilityService,
        private BookingAvailabilityChecker $availabilityChecker,
        private BookingInvoiceService $invoiceService
    ) {}

    public function execute(CreateBookingData $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $designer = Designer::with('employee')->findOrFail($data->designer_id);
            $service = DesignService::findOrFail($data->service_id);

            if ($service->is_schedule_blocking) {
                $errors = $this->availabilityChecker->validateBookingSlots(
                    $designer,
                    $data->date,
                    $data->start_hour,
                    $data->end_hour
                );

                if (! empty($errors)) {
                    throw new \InvalidArgumentException(implode('. ', $errors));
                }
            }

            $startAt = Carbon::parse($data->date)->setHour($data->start_hour)->startOfHour();
            $endAt = Carbon::parse($data->date)->setHour($data->end_hour)->startOfHour();

            if (! $service->is_schedule_blocking && $data->deadline_at) {
                $deadlineAt = Carbon::parse($data->deadline_at);
            } else {
                $deadlineAt = $service->deadline_days
                    ? now()->addDays($service->deadline_days)
                    : null;
            }

            $status = BookingStatus::PendingDeposit;

            $booking = Booking::create([
                'customer_id' => $data->customer_id,
                'customer_name' => $data->customer_name,
                'customer_email' => $data->customer_email,
                'customer_phone' => $data->customer_phone,
                'designer_id' => $designer->id,
                'service_id' => $service->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'deadline_at' => $deadlineAt,
                'status' => $status,
            ]);

            if ($service->is_schedule_blocking) {
                BookingSession::create([
                    'booking_id' => $booking->id,
                    'date' => $data->date,
                    'start_hour' => $data->start_hour,
                    'end_hour' => $data->end_hour,
                ]);
            }

            $depositInvoice = $this->invoiceService->createDepositInvoice($booking);

            return $booking->load(['designer', 'service', 'sessions', 'depositInvoice']);
        });
    }
}
