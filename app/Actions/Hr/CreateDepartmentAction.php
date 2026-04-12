<?php

namespace App\Actions\Hr;

use App\Models\Hr\Department;

class CreateDepartmentAction
{
    public function execute(array $data): Department
    {
        return Department::create($data);
    }
}
