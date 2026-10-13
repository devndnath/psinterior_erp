<?php

namespace App\Policies;

use App\Models\Designation;
use App\Models\User;

/**
 * Same reasoning as DepartmentPolicy — Designation only ever exists
 * under a Department (Employee.designation_id), gated on
 * `manage_employees` rather than given its own CRUD permission set.
 */
class DesignationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }

    public function view(User $user, Designation $designation): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }

    public function update(User $user, Designation $designation): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }

    public function delete(User $user, Designation $designation): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }
}
