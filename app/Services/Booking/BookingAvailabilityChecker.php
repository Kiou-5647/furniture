<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking\BookingSession;
use App\Models\Hr\Designer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingAvailabilityChecker
{
    public function __construct(
        private DesignerAvailabilityService $availabilityService
    ) {}

    public function isSlotAvailable(
        Designer $designer,
        string $date,
        int $startHour,
        int $endHour
    ): bool {
        $availableSlots = $this->availabilityService->getAvailableSlotsForDate($designer, $date);

        for ($h = $startHour; $h < $endHour; $h++) {
            if (! isset($availableSlots[$h]) || ! $availableSlots[$h]) {
                return false;
            }
        }

        return ! $this->hasConflictingBooking($designer, $date, $startHour, $endHour);
    }

    public function hasConflictingBooking(
        Designer $designer,
        string $date,
        int $startHour,
        int $endHour
    ): bool {
        return BookingSession::whereHas('booking', function ($query) use ($designer) {
            $query->where('designer_id', $designer->id)
                ->whereIn('status', [
                    BookingStatus::PendingDeposit->value,
                    BookingStatus::PendingConfirmation->value,
                    BookingStatus::Confirmed->value,
                ]);
        })
            ->where('date', $date)
            ->where(function ($query) use ($startHour, $endHour) {
                $query->where(function ($q) use ($startHour, $endHour) {
                    $q->where('start_hour', '<', $endHour)
                        ->where('end_hour', '>', $startHour);
                });
            })
            ->exists();
    }

    public function getAvailableSlots(
        Designer $designer,
        string $date,
        ?int $minDuration = null,
        ?int $maxDuration = null
    ): array {
        $availableSlots = $this->availabilityService->getAvailableSlotsForDate($designer, $date);

        $bookedSessions = $this->getBookedSessions($designer, $date);

        $blockedSlots = [];
        foreach ($bookedSessions as $session) {
            for ($h = $session->start_hour; $h < $session->end_hour; $h++) {
                $blockedSlots[$h] = true;
            }
        }

        $result = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $isAvailable = ($availableSlots[$hour] ?? false) && ! ($blockedSlots[$hour] ?? false);

            $duration = $this->getConsecutiveAvailableHours($availableSlots, $blockedSlots, $hour);
            $duration = min($duration, 24 - $hour);

            if ($minDuration && $duration < $minDuration) {
                continue;
            }
            if ($maxDuration && $duration > $maxDuration) {
                $duration = $maxDuration;
            }

            $result[$hour] = [
                'available' => $isAvailable,
                'duration' => $duration,
            ];
        }

        return $result;
    }

    private function getConsecutiveAvailableHours(array $available, array $blocked, int $startHour): int
    {
        $count = 0;
        for ($h = $startHour; $h < 24; $h++) {
            if (($available[$h] ?? false) && ! ($blocked[$h] ?? false)) {
                $count++;
            } else {
                break;
            }
        }

        return $count;
    }

    private function getBookedSessions(Designer $designer, string $date): Collection
    {
        $dateStr = Carbon::parse($date)->toDateString();

        return BookingSession::whereHas('booking', function ($query) use ($designer) {
            $query->where('designer_id', $designer->id)
                ->whereIn('status', [
                    BookingStatus::PendingDeposit->value,
                    BookingStatus::PendingConfirmation->value,
                    BookingStatus::Confirmed->value,
                ]);
        })
            ->where('date', $dateStr)
            ->get();
    }

    public function validateBookingSlots(
        Designer $designer,
        string $date,
        int $startHour,
        int $endHour
    ): array {
        $errors = [];

        if ($startHour < 0 || $startHour > 23) {
            $errors[] = 'Giờ bắt đầu không hợp lệ';
        }

        if ($endHour < 1 || $endHour > 24) {
            $errors[] = 'Giờ kết thúc không hợp lệ';
        }

        if ($endHour <= $startHour) {
            $errors[] = 'Giờ kết thúc phải lớn hơn giờ bắt đầu';
        }

        if (! $this->isSlotAvailable($designer, $date, $startHour, $endHour)) {
            $errors[] = 'Khung giờ này đã được đặt hoặc không có sẵn';
        }

        $carbonDate = Carbon::parse($date);
        $advanceDays = $this->getAdvanceBookingDays();
        $maxDate = now()->addDays($advanceDays);
        if ($carbonDate->gt($maxDate)) {
            $errors[] = "Chỉ có thể đặt trước tối đa {$advanceDays} ngày";
        }

        if ($carbonDate->isBefore(today())) {
            $errors[] = 'Không thể đặt cho ngày trong quá khứ';
        }

        return $errors;
    }

    private function getAdvanceBookingDays(): int
    {
        return 30;
    }
}
