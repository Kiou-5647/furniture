<?php

namespace App\Actions\Hr;

use App\Enums\UserType;
use App\Models\Auth\User;
use App\Models\Hr\Designer;
use App\Models\Hr\Employee;
use App\Services\Hr\DesignerService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateDesignerAction
{
    public function execute(array $data, ?UploadedFile $avatarFile = null): Designer
    {
        $designerService = new DesignerService();

        return DB::transaction(function () use ($data, $avatarFile, $designerService) {
            $availabilities = $data['availabilities'] ?? [];
            unset($data['availabilities']);

            // Determine or create User
            if (isset($data['employee_id'])) {
                // Link to employee's user
                $employee = Employee::findOrFail($data['employee_id']);
                $data['user_id'] = $employee->user_id;
            } else {
                throw new \Exception('Vui lòng chọn nhân viên!');
            }

            $avatarFile = $data['avatar'] ?? null;
            unset($data['avatar']);
            unset($data['email']);

            $designer = Designer::create($data);

            if ($avatarFile instanceof UploadedFile) {
                $designer->addMedia($avatarFile)->toMediaCollection('avatar');
            }

            $designerService->setWeeklySlots($designer, $availabilities);

            return $designer->load(['user', 'employee', 'availabilitySlots']);
        });
    }
}
