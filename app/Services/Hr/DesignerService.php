<?php

namespace App\Services\Hr;

use App\Data\Hr\DesignerFilterData;
use App\Models\Booking\DesignerAvailabilitySlot;
use App\Models\Hr\Designer;
use App\Models\Hr\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DesignerService
{
    public function getFiltered(DesignerFilterData $filter): LengthAwarePaginator
    {
        $allowedSortColumns = ['full_name', 'hourly_rate', 'created_at'];
        $orderBy = in_array($filter->order_by, $allowedSortColumns, true) ? $filter->order_by : 'created_at';
        $orderDirection = $filter->order_direction === 'asc' ? 'asc' : 'desc';

        return Designer::query()
            ->with(['user', 'employee.user', 'availabilitySlots'])
            ->when($filter->search, fn($q) => $q->search($filter->search))
            ->when($filter->is_active !== null, fn($q) => $q->byActiveStatus($filter->is_active))
            ->orderBy($orderBy, $orderDirection)
            ->paginate($filter->per_page);
    }

    public function getById(string $id): Designer
    {
        return Designer::with(['user', 'employee.user', 'availabilitySlots'])->findOrFail($id);
    }

    public function getActiveOptions(): Collection
    {
        return Designer::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'hourly_rate'])
            ->map(fn(Designer $designer) => [
                'value' => $designer->id,
                'label' => $designer->full_name,
                'hourly_rate' => $designer->hourly_rate,
            ]);
    }

    public function getEmployeeOptions(): Collection
    {
        return Employee::query()
            ->whereNull('termination_date')
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('designers')
                    ->whereColumn('designers.employee_id', 'employees.id')
                    ->whereNull('deleted_at');
            })
            ->with('user')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone', 'user_id'])
            ->map(fn($e) => [
                'id' => $e->id,
                'full_name' => $e->full_name,
                'phone' => $e->phone,
                'email' => $e->user?->email,
            ]);
    }

    public function getWeeklySlots(Designer $designer): array
    {
        $slots = $designer->availabilitySlots()->get();

        $weekly = [];
        for ($day = 0; $day < 7; $day++) {
            $weekly[$day] = array_fill(0, 24, false);
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
}
