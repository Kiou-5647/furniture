<?php

namespace App\Http\Controllers\Employee\Setting;

use App\Http\Requests\Setting\ActivityLog\ActivityLogIndexRequest;
use App\Http\Resources\Employee\Setting\ActivityLogResource;
use App\Services\Setting\ActivityLogService;

class ActivityLogController
{
    public function __construct(private ActivityLogService $service) {}

    public function index(ActivityLogIndexRequest $request)
    {
        $activities = $this->service->getLogsForSubject(
            $request->validated('subject_type'),
            $request->validated('subject_id')
        );

        return ActivityLogResource::collection($activities);
    }
}
