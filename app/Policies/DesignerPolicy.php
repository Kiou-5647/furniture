<?php

namespace App\Policies;

use App\Constants\Permission;
use App\Models\Auth\User;
use App\Models\Hr\Designer;

class DesignerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DESIGNER['SELECT']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DESIGNER['CREATE']);
    }

    public function update(User $user, Designer $designer): bool
    {
        // 1. Check quyền cập nhật
        if ($user->hasPermissionTo(Permission::DESIGNER['UPDATE'])) {
            return true;
        }

        // 2. Cho phép Designer tự cập nhật chính mình
        return $designer->user_id === $user->id;
    }

    public function delete(User $user, Designer $designer): bool
    {
        return $user->hasPermissionTo(Permission::DESIGNER['DELETE']);
    }
}
