<?php

namespace App\Actions\Hr;

use App\Models\Hr\Employee;
use Illuminate\Support\Carbon;

class TerminateEmployeeAction
{
    public function execute(Employee $employee, ?Carbon $terminationDate = null): Employee
    {
        $employee->update([
            'termination_date' => $terminationDate ?? now(),
        ]);

        $employee->user->update([
            'is_active' => false,
        ]);

        return $employee->fresh(['user']);
    }
}
