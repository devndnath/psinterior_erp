<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_employees');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->isStaff() && $user->can('view_employees');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_employees');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->isStaff() && $user->can('edit_employees');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->isStaff() && $user->can('delete_employees');
    }
}
