<?php

namespace App\Actions\Hr;

use App\Models\Hr\Department;

class UpdateDepartmentAction
{
    public function execute(Department $department, array $data): Department
    {
        $department->update($data);

        return $department->fresh(['manager']);
    }
}
