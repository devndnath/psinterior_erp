<?php

namespace App\Policies;

use App\Models\Design;
use App\Models\User;

class DesignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_designs');
    }

    public function view(User $user, Design $design): bool
    {
        return $user->isStaff() && $user->can('view_designs');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_designs');
    }

    public function update(User $user, Design $design): bool
    {
        return $user->isStaff() && $user->can('edit_designs');
    }

    public function delete(User $user, Design $design): bool
    {
        return $user->isStaff() && $user->can('delete_designs');
    }

    /**
     * Uploading a new revision is a distinct, narrower ability from
     * editing a design's own metadata (title/category/description) — a
     * Designer typically has this without `edit_designs` (see
     * RolePermissionSeeder's `designer` role).
     */
    public function uploadRevision(User $user, Design $design): bool
    {
        return $user->isStaff() && $user->can('upload_design');
    }

    /**
     * Recording a client's decision on a revision — a managerial action
     * distinct from uploading the work itself (see `approve_design` in
     * RolePermissionSeeder's `$extraAbilities`).
     */
    public function recordApproval(User $user, Design $design): bool
    {
        return $user->isStaff() && $user->can('approve_design');
    }
}
