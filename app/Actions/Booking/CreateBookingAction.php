<?php

namespace App\Actions\Booking;

use App\Data\Booking\CreateBookingData;
use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use App\Models\Hr\Designer;
use App\Services\Booking\BookingAvailabilityService;
use App\Services\Booking\BookingInvoiceService;
use App\Settings\BookingSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateBookingAction
{
    public function __construct(
        private BookingAvailabilityService $availabilityChecker,
        private BookingInvoiceService $invoiceService,
        private BookingSettings $settings,
    ) {}

    public function execute(CreateBookingData $data): Booking
    {
        $designer = Designer::with('employee')->findOrFail($data->designer_id);

        $startAt = Carbon::parse($data->date . ' ' . $data->start_time);
        $endAt = (clone $startAt)->addHours($data->duration);

        if (!$this->availabilityChecker->isAvailable($designer, $startAt, $endAt)) {
            throw new \InvalidArgumentException('Nhà thiết kế không sẵn sàng vào khung giờ này.');
        }

        return DB::transaction(function () use ($designer, $data, $startAt, $endAt) {

            $totalPrice = $designer->hourly_rate * $data->duration;


            $booking = Booking::create([
                'booking_number' => Booking::generateBookingNumber(),
                'customer_id'    => $data->customer_id,
                'customer_name'  => $data->customer_name,
                'customer_email' => $data->customer_email,
                'customer_phone' => $data->customer_phone,
                'province_code'  => $data->province_code,
                'ward_code'      => $data->ward_code,
                'province_name'  => $data->province_name,
                'ward_name'      => $data->ward_name,
                'street'         => $data->street,
                'designer_id'    => $designer->id,
                'start_at'       => $startAt,
                'end_at'         => $endAt,
                'total_price'    => $totalPrice,
                'notes'          => $data->notes,
                'status'         => BookingStatus::PendingDeposit,
            ]);

            $this->invoiceService->createDepositInvoice($booking, $this->settings->deposit_percentage);

            Log::info('End DB transaction!');
            return $booking->load(['designer', 'depositInvoice']);
        });
    }
}
