<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_inventory');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->isStaff() && $user->can('view_inventory');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_inventory');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isStaff() && $user->can('edit_inventory');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isStaff() && $user->can('delete_inventory');
    }
}
