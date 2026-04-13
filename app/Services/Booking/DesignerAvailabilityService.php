<?php

namespace App\Services\Booking;

use App\Models\Booking\DesignerAvailabilitySlot;
use App\Models\Hr\Designer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DesignerAvailabilityService
{
    public function getWeeklySlots(Designer $designer): array
    {
        $slots = $designer->availabilitySlots()->get();

        $weekly = [];
        for ($day = 0; $day < 7; $day++) {
            $daySlots = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $daySlots[$hour] = false;
            }
            $weekly[$day] = $daySlots;
        }

        foreach ($slots as $slot) {
            $weekly[$slot->day_of_week][$slot->hour] = $slot->is_available;
        }

        return $weekly;
    }

    public function setWeeklySlots(Designer $designer, array $slots): void
    {
        $now = now();

        $records = [];
        foreach ($slots as $slot) {
            $records[] = [
                'designer_id' => $designer->id,
                'day_of_week' => $slot['day_of_week'],
                'hour' => $slot['hour'],
                'is_available' => $slot['is_available'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DesignerAvailabilitySlot::upsert(
            $records,
            ['designer_id', 'day_of_week', 'hour'],
            ['is_available', 'updated_at']
        );
    }

    public function getAvailableSlotsForDate(Designer $designer, string $date): array
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek;

        $slots = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $slots[$hour] = false;
        }

        $weeklySlots = $designer->availabilitySlots()
            ->where('day_of_week', $dayOfWeek)
            ->get();

        foreach ($weeklySlots as $slot) {
            $slots[$slot->hour] = $slot->is_available;
        }

        return $slots;
    }

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
}
