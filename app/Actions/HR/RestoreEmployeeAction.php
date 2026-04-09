<?php

namespace App\Actions\HR;

use App\Models\Employee\Employee;

class RestoreEmployeeAction
{
    public function execute(Employee $employee): Employee
    {
        $employee->update([
            'termination_date' => null,
        ]);

        $employee->user->update([
            'is_active' => true,
        ]);

        return $employee->fresh(['user']);
    }
}
