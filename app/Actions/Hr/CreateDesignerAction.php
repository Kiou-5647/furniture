<?php

namespace App\Actions\Hr;

use App\Models\Hr\Designer;
use App\Models\Hr\Employee;
use App\Services\Hr\DesignerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

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

                $user = $employee->user;
                $user->givePermissionTo([
                    'Xem lịch thiết kế',
                    'Tạo lịch thiết kế',
                    'Sửa lịch thiết kế',
                ]);
            } else {
                throw new \Exception('Vui lòng chọn nhân viên!');
            }

            $avatarFile = $data['avatar'] ?? null;
            unset($data['avatar']);
            unset($data['email']);

            $designer = Designer::create($data);

            if ($avatarFile instanceof UploadedFile) {
                $designer->addMedia($avatarFile)->toMediaCollection('avatar');
                $designer->touch();
            }

            $designerService->setWeeklySlots($designer, $availabilities);

            return $designer->load(['user', 'employee', 'availabilitySlots']);
        });
    }
}
