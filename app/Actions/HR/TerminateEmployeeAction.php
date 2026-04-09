<?php

namespace App\Actions\HR;

use App\Models\Employee\Employee;
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
