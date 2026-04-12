<?php

namespace App\Actions\Fortify;

use App\Models\Auth\User;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use Illuminate\Support\Facades\DB;

class CreateEmployeeProfile
{
    /**
     * Create an employee profile for the given user.
     */
    public function create(User $user, array $input = []): Employee
    {
        return DB::transaction(function () use ($user, $input) {
            // Get or create default department
            $department = Department::firstOrCreate(
                ['code' => 'GENERAL'],
                [
                    'name' => 'General',
                    'description' => 'General department for all employees',
                ]
            );

            return $user->employee()->create([
                'department_id' => $department->id,
                'full_name' => $input['name'] ?? $user->name,
                'phone' => $input['phone'] ?? null,
                'hire_date' => now(),
            ]);
        });
    }
}
