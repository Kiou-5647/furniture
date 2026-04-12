<?php

namespace App\Actions\Hr;

use App\Models\Hr\Employee;

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
