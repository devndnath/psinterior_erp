<?php

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\User;

/**
 * An admin-editable reference list, same spirit as ExpenseCategoryPolicy
 * — gated entirely on `manage_settings` rather than its own CRUD
 * permission set.
 */
class ProductCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function view(User $user, ProductCategory $productCategory): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function update(User $user, ProductCategory $productCategory): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }

    public function delete(User $user, ProductCategory $productCategory): bool
    {
        return $user->isStaff() && $user->can('manage_settings');
    }
}
