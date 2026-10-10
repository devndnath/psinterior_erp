<?php

namespace App\Policies;

use App\Models\Handover;
use App\Models\User;

/**
 * Standard CRUD on `*_handovers`. confirmByClient() is also gated on
 * `edit_handovers` — staff recording the client's confirmation on their
 * behalf (no client portal yet), same reasoning as SnagPolicy's
 * transition methods.
 */
class HandoverPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_handovers');
    }

    public function view(User $user, Handover $handover): bool
    {
        return $user->isStaff() && $user->can('view_handovers');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_handovers');
    }

    public function update(User $user, Handover $handover): bool
    {
        return $user->isStaff() && $user->can('edit_handovers');
    }

    public function delete(User $user, Handover $handover): bool
    {
        return $user->isStaff() && $user->can('delete_handovers');
    }

    public function confirmByClient(User $user, Handover $handover): bool
    {
        return $user->isStaff() && $user->can('edit_handovers');
    }
}
