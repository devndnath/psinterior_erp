<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

/**
 * An admin-editable reference list, same spirit as ExpenseCategoryPolicy
 * — gated entirely on `manage_settings` rather than its own CRUD
 * permission set.
 */
class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }
}
