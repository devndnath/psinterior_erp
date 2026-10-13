<?php

namespace App\Policies;

use App\Models\SelectionBoard;
use App\Models\User;

class SelectionBoardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_material_selections');
    }

    public function view(User $user, SelectionBoard $selectionBoard): bool
    {
        return $user->isStaff() && $user->can('view_material_selections');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_material_selections');
    }

    public function update(User $user, SelectionBoard $selectionBoard): bool
    {
        return $user->isStaff() && $user->can('edit_material_selections');
    }

    public function delete(User $user, SelectionBoard $selectionBoard): bool
    {
        return $user->isStaff() && $user->can('delete_material_selections');
    }

    /**
     * Adding/removing options and sending the board to the client is a
     * distinct, narrower ability from editing the board's own metadata —
     * same split as Design's `uploadRevision` vs `update`.
     */
    public function manageOptions(User $user, SelectionBoard $selectionBoard): bool
    {
        return $user->isStaff() && $user->can('edit_material_selections');
    }

    /**
     * Recording the client's decision on their behalf — a managerial
     * action distinct from assembling the options themselves (see
     * `decide_material_selection` in RolePermissionSeeder's
     * `$extraAbilities`, mirroring `approve_design`).
     */
    public function recordDecision(User $user, SelectionBoard $selectionBoard): bool
    {
        return $user->isStaff() && $user->can('decide_material_selection');
    }
}
