<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * Reference pattern for every policy added in later phases (spec §61:
 * Controller -> Form Request -> Service -> Model, authorization via
 * Policies/Gates, never hard-coded in a controller). App\Models\Project
 * itself ships in Phase 3 — this class is not yet registered in
 * AuthServiceProvider::$policies, so it has no effect until then, but
 * fixes the shape every other module's policy should follow:
 *
 *   - viewAny/view gated by a `view_*` permission
 *   - create/update/delete gated by matching `create_*`/`edit_*`/`delete_*`
 *   - an extra ownership/assignment check where the business rule calls
 *     for it (a client may only ever see their own records — spec §78
 *     rule 10 — a pattern ClientPolicy, InvoicePolicy, etc. will reuse)
 *   - Super Admin's blanket bypass lives once, centrally, in
 *     AuthServiceProvider::boot()'s Gate::before — no policy re-implements it.
 */
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_projects');
    }

    public function view(User $user, Project $project): bool
    {
        if (! $user->isStaff() || ! $user->can('view_projects')) {
            return false;
        }

        // Non-managers only see projects they are actually assigned to;
        // Project Managers/Admins with view_projects see everything.
        // (ProjectService::isMember() lands with the Project model itself
        // in Phase 3 — placeholder call shown for shape only.)
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_projects');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isStaff() && $user->can('edit_projects');
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isStaff() && $user->can('delete_projects');
    }
}
