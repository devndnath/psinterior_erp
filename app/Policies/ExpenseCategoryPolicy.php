<?php

namespace App\Policies;

use App\Models\ExpenseCategory;
use App\Models\User;

/**
 * An admin-editable reference list, same spirit as Settings — gated
 * entirely on `manage_settings` rather than its own CRUD permission set.
 */
class ExpenseCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function view(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function update(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function delete(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }
}
