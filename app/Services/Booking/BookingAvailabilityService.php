<?php

namespace App\Services\Booking;

use App\Models\Booking\Booking;
use App\Models\Booking\DesignerAvailabilitySlot;
use App\Models\Hr\Designer;
use Carbon\Carbon;

class BookingAvailabilityService
{
    /**
     * Kiểm tra lịch trống của nhà thiết kế trong khung giờ cụ thể
     */
    public function isAvailable(Designer $designer, Carbon $start, Carbon $end): bool
    {
        if (!$this->isWithinGeneralAvailability($designer, $start, $end)) {
            return false;
        }

        return !$this->hasBookingConflict($designer, $start, $end);
    }

    /**
     * [GRID LOGIC]
     * Returns "Actual" availability for a specific date (General Slots MINUS Bookings).
     * Used by the customer-facing booking calendar.
     */
    public function getAvailableSlotsForDate(Designer $designer, string $date): array
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek;

        // 1. Start with the general template
        $slots = array_fill(0, 24, false);
        $generalSlots = DesignerAvailabilitySlot::where([
            'designer_id' => $designer->id,
            'day_of_week' => $dayOfWeek,
        ])->get();

        foreach ($generalSlots as $slot) {
            $slots[$slot->hour] = $slot->is_available;
        }

        // 2. Subtract actual bookings for this date
        $bookings = Booking::where('designer_id', $designer->id)
            ->whereDate('start_at', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->start_at);
            $end = Carbon::parse($booking->end_at);

            $actualStart = $start->isSameDay(Carbon::parse($date)) ? $start->hour : 0;
            $actualEnd = $end->isSameDay(Carbon::parse($date)) ? $end->hour : 24;

            for ($h = $actualStart; $h < $actualEnd; $h++) {
                if ($h >= 0 && $h < 24) {
                    $slots[$h] = false;
                }
            }
        }

        return $slots;
    }

    /**
     * Kiểm tra xem khung giờ có nằm trong giờ làm việc tổng quá của nhà thiết kế không
     */
    private function isWithinGeneralAvailability(Designer $designer, Carbon $start, Carbon $end): bool
    {
        $current = $start->copy();

        while ($current < $end) {
            $dayOfWeek = $current->dayOfWeek;
            $hour = $current->hour;

            $available = DesignerAvailabilitySlot::where([
                'designer_id' => $designer->id,
                'day_of_week' => $dayOfWeek,
                'hour' => $hour,
                'is_available' => true,
            ])->exists();

            if (!$available) return false;
            $current->addHour();
        }
        return true;
    }


    /**
     * Kiểm tra xem khung giờ có bị trùng với lịch hẹn nào không
     */
    private function hasBookingConflict(Designer $designer, Carbon $start, Carbon $end): bool
    {
        return Booking::query()
            ->where('designer_id', $designer->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($start, $end) {
                $query->where('start_at', '<', $end)
                    ->where('end_at', '>', $start);
            })
            ->exists();
    }
}
