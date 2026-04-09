<?php

namespace App\Actions\Booking;

use App\Data\Booking\CreateBookingData;
use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use App\Models\Booking\Designer;
use Illuminate\Support\Facades\DB;

class CreateBookingAction
{
    public function execute(CreateBookingData $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $designer = Designer::findOrFail($data->designer_id);

            // If designer has auto_confirm, skip confirmation step
            $status = $designer->auto_confirm_bookings
                ? BookingStatus::Confirmed
                : BookingStatus::PendingConfirmation;

            $booking = Booking::create([
                'customer_id' => $data->customer_id,
                'designer_id' => $data->designer_id,
                'service_id' => $data->service_id,
                'start_at' => $data->start_at,
                'end_at' => $data->end_at,
                'deadline_at' => $data->deadline_at,
                'status' => $status,
            ]);

            return $booking->load(['designer', 'service']);
        });
    }
}
