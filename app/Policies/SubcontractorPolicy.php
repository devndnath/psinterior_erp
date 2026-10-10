<?php

namespace App\Policies;

use App\Models\Subcontractor;
use App\Models\User;

class SubcontractorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_subcontractors');
    }

    public function view(User $user, Subcontractor $subcontractor): bool
    {
        return $user->isStaff() && $user->can('view_subcontractors');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_subcontractors');
    }

    public function update(User $user, Subcontractor $subcontractor): bool
    {
        return $user->isStaff() && $user->can('edit_subcontractors');
    }

    public function delete(User $user, Subcontractor $subcontractor): bool
    {
        return $user->isStaff() && $user->can('delete_subcontractors');
    }
}
