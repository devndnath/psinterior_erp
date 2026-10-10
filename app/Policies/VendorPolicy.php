<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_vendors');
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $user->isStaff() && $user->can('view_vendors');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_vendors');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->isStaff() && $user->can('edit_vendors');
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->isStaff() && $user->can('delete_vendors');
    }
}
