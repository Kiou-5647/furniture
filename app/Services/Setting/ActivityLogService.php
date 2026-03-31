<?php

namespace App\Services\Setting;

use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    public function getLogsForSubject(string $type, string $id, int $perPage = 15): LengthAwarePaginator
    {
        return Activity::query()
            ->with('causer')
            ->where('subject_type', $type)
            ->where('subject_id', $id)
            ->latest()
            ->paginate($perPage);
    }
}
