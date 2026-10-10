<?php

namespace App\Policies;

use App\Models\Inspection;
use App\Models\User;

class InspectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_inspections');
    }

    public function view(User $user, Inspection $inspection): bool
    {
        return $user->isStaff() && $user->can('view_inspections');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_inspections');
    }

    public function update(User $user, Inspection $inspection): bool
    {
        return $user->isStaff() && $user->can('edit_inspections');
    }

    public function delete(User $user, Inspection $inspection): bool
    {
        return $user->isStaff() && $user->can('delete_inspections');
    }
}
