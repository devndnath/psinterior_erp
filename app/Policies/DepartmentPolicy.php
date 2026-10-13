<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

/**
 * Department is HR reference data (Employee.department_id), not a
 * full CRUD module of its own — same spirit as ExpenseCategoryPolicy
 * ("an admin-editable reference list"), but gated on `manage_employees`
 * rather than `manage_settings` since this is workforce data an
 * hr-manager already has that ability for (RolePermissionSeeder), not
 * general company settings.
 */
class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->isStaff() && $user->can('manage_employees');
    }
}
