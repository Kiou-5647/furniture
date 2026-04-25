<?php

namespace App\Actions\Hr;

use App\Models\Hr\Employee;
use Illuminate\Http\UploadedFile;

class UpdateEmployeeAction
{
    public function execute(Employee $employee, array $data, ?array $roles = null, ?array $permissions = null): Employee
    {
        $avatarFile = $data['avatar'] ?? null;
        unset($data['avatar']);

        $userData = [];
        $employeeData = [];

        if (isset($data['name'])) {
            $userData['name'] = $data['name'];
        }

        if (isset($data['email']) && $data['email'] !== $employee->user->email) {
            $userData['email'] = $data['email'];
            $userData['email_verified_at'] = null;
        }

        if (isset($data['is_active'])) {
            $userData['is_active'] = (bool) $data['is_active'];
        }

        foreach (['full_name', 'phone', 'department_id', 'hire_date'] as $key) {
            if (isset($data[$key])) {
                $employeeData[$key] = $data[$key];
            }
        }

        if (! empty($userData)) {
            $employee->user->update($userData);
        }

        if (! empty($employeeData)) {
            $employee->update($employeeData);
        }

        if ($avatarFile instanceof UploadedFile) {
            $employee->clearMediaCollection('avatar');
            $employee->addMedia($avatarFile)->toMediaCollection('avatar');
        }

        if ($roles !== null) {
            $employee->user->syncRoles($roles);
        }

        if ($permissions !== null) {
            $employee->user->syncPermissions($permissions);
        }

        return $employee->fresh(['user', 'department']);
    }
}
