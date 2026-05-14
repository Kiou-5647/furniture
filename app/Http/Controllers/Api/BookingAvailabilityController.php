<?php

namespace App\Http\Controllers\Api;

use App\Models\Hr\Designer;
use App\Services\Booking\BookingAvailabilityService;
use App\Services\Hr\DesignerService;
use App\Settings\WorkHourSettings;
use Illuminate\Http\Request;

class BookingAvailabilityController
{
    public function __construct(
        private WorkHourSettings $settings,
        private DesignerService $service,
        private BookingAvailabilityService $checker
    ) {}

    public function availabilities(Designer $designer)
    {
        $weeklySlots = $this->service->getWeeklySlots($designer);

        return response()->json([
            'weekly' => $weeklySlots,
        ]);
    }

    public function availableSlots(Request $request, Designer $designer)
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $slots = $this->checker->getAvailableSlotsForDate($designer, $request->input('date'));

        $slotsMap = [];
        for ($hour = 5; $hour <= 23; $hour++) {
            $slotsMap[$hour] = $slots[$hour] ? 1 : 0;
        }

        return response()->json([
            'date' => $request->input('date'),
            'slots' => $slotsMap,
        ]);
    }
}
