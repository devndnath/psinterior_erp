<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warranty;

class WarrantyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_warranties');
    }

    public function view(User $user, Warranty $warranty): bool
    {
        return $user->isStaff() && $user->can('view_warranties');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_warranties');
    }

    public function update(User $user, Warranty $warranty): bool
    {
        return $user->isStaff() && $user->can('edit_warranties');
    }

    public function delete(User $user, Warranty $warranty): bool
    {
        return $user->isStaff() && $user->can('delete_warranties');
    }
}
