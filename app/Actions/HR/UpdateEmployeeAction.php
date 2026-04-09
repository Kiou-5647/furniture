<?php

namespace App\Actions\HR;

use App\Models\Employee\Employee;

class UpdateEmployeeAction
{
    public function execute(Employee $employee, array $data): Employee
    {
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

        return $employee->fresh(['user', 'department']);
    }
}
