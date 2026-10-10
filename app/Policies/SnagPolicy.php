<?php

namespace App\Policies;

use App\Models\Snag;
use App\Models\User;

/**
 * Standard CRUD on `*_snags`. All the status-transition methods
 * (assign/startProgress/markFixed/verify/close/reopen) are gated on
 * `edit_snags` — no separate approval ability exists for them, the same
 * documented simplification as Phase 9's leave approval
 * (LeavePolicy).
 */
class SnagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_snags');
    }

    public function view(User $user, Snag $snag): bool
    {
        return $user->isStaff() && $user->can('view_snags');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_snags');
    }

    public function update(User $user, Snag $snag): bool
    {
        return $user->isStaff() && $user->can('edit_snags');
    }

    public function delete(User $user, Snag $snag): bool
    {
        return $user->isStaff() && $user->can('delete_snags');
    }

    public function assign(User $user, Snag $snag): bool
    {
        return $user->isStaff() && $user->can('edit_snags');
    }

    public function startProgress(User $user, Snag $snag): bool
    {
        return $user->isStaff() && $user->can('edit_snags');
    }

    public function markFixed(User $user, Snag $snag): bool
    {
        return $user->isStaff() && $user->can('edit_snags');
    }

    public function verify(User $user, Snag $snag): bool
    {
        return $user->isStaff() && $user->can('edit_snags');
    }

    public function close(User $user, Snag $snag): bool
    {
        return $user->isStaff() && $user->can('edit_snags');
    }

    public function reopen(User $user, Snag $snag): bool
    {
        return $user->isStaff() && $user->can('edit_snags');
    }
}
