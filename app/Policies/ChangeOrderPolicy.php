<?php

namespace App\Policies;

use App\Models\ChangeOrder;
use App\Models\User;

class ChangeOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_change_orders');
    }

    public function view(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->isStaff() && $user->can('view_change_orders');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_change_orders');
    }

    public function update(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->isStaff() && $user->can('edit_change_orders');
    }

    public function delete(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->isStaff() && $user->can('delete_change_orders');
    }

    /**
     * Recording the client's decision is gated by the distinct
     * `approve_change_order` ability already seeded in Phase 1 (it's the
     * action that mutates projects.contract_value, per §19 — a
     * deliberately narrower gate than general edit).
     */
    public function recordDecision(User $user, ChangeOrder $changeOrder): bool
    {
        return $user->isStaff() && $user->can('approve_change_order');
    }
}
