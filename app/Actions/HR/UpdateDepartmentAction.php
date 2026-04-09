<?php

namespace App\Actions\HR;

use App\Models\Employee\Department;

class UpdateDepartmentAction
{
    public function execute(Department $department, array $data): Department
    {
        $department->update($data);

        return $department->fresh(['manager']);
    }
}
