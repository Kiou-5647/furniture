<?php

namespace App\Services\Booking;

use App\Models\Booking\Booking;
use App\Models\Booking\DesignerAvailabilitySlot;
use App\Models\Hr\Designer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingAvailabilityService
{
    /**
     * [CHECKER LOGIC]
     * Check if a designer is available for a specific time window.
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
            $startHour = $booking->start_at->hour;
            $endHour = $booking->end_at->hour;

            for ($h = $startHour; $h < $endHour; $h++) {
                if ($h >= 0 && $h < 24) {
                    $slots[$h] = false;
                }
            }
        }

        return $slots;
    }

    /**
     * [GRID LOGIC]
     * Returns available dates and their hours within a range.
     */
    public function getAvailableDates(Designer $designer, string $startDate, string $endDate): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $dates = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            $slots = $this->getAvailableSlotsForDate($designer, $dateStr);
            $hasAvailability = in_array(true, $slots, true);

            $dates[] = [
                'date' => $dateStr,
                'has_availability' => $hasAvailability,
                'available_hours' => array_keys(array_filter($slots)),
            ];

            $current->addDay();
        }

        return collect($dates);
    }

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
