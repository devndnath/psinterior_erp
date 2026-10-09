<?php

namespace App\Policies;

use App\Models\Boq;
use App\Models\User;

class BoqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_boqs');
    }

    public function view(User $user, Boq $boq): bool
    {
        return $user->isStaff() && $user->can('view_boqs');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_boqs');
    }

    public function update(User $user, Boq $boq): bool
    {
        return $user->isStaff() && $user->can('edit_boqs');
    }

    public function delete(User $user, Boq $boq): bool
    {
        return $user->isStaff() && $user->can('delete_boqs');
    }
}
