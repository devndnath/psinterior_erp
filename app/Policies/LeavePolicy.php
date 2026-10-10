<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;

/**
 * Permission-gating decision (see RolePermissionSeeder and PHASE9_SUMMARY.md
 * for the full writeup): `edit_leaves` is the ONLY leaves-related
 * ability any role besides super-admin holds — only `hr-manager` holds
 * it. ARCHITECTURE.md §3.9's two-stage approval (`manager_status` +
 * `hr_status`) is a data-model distinction, not a permissions one: there
 * is no separate "manager approval" ability seeded anywhere, and
 * inventing one here (e.g. `approve_leave_manager`) would be an
 * unseeded permission string that silently locks every role out of it
 * except super-admin — exactly the pitfall this phase was warned about.
 * So `approveByManager()`, `approveByHr()` and `reject()` are ALL gated
 * on the same existing `edit_leaves` ability. Today that means
 * hr-manager performs both steps; a future phase can split this once a
 * distinct ability is actually seeded for a manager-only role.
 */
class LeavePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_leaves');
    }

    public function view(User $user, Leave $leave): bool
    {
        return $user->isStaff() && $user->can('view_leaves');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_leaves');
    }

    public function update(User $user, Leave $leave): bool
    {
        return $user->isStaff() && $user->can('edit_leaves');
    }

    public function delete(User $user, Leave $leave): bool
    {
        return $user->isStaff() && $user->can('delete_leaves');
    }

    public function approveByManager(User $user, Leave $leave): bool
    {
        return $user->isStaff() && $user->can('edit_leaves');
    }

    public function approveByHr(User $user, Leave $leave): bool
    {
        return $user->isStaff() && $user->can('edit_leaves');
    }

    public function reject(User $user, Leave $leave): bool
    {
        return $user->isStaff() && $user->can('edit_leaves');
    }
}
