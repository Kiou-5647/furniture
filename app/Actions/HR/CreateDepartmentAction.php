<?php

namespace App\Actions\HR;

use App\Models\Employee\Department;

class CreateDepartmentAction
{
    public function execute(array $data): Department
    {
        return Department::create($data);
    }
}
