<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * The reference pattern every later-phase policy follows (spec §61:
 * Controller -> Form Request -> Service -> Model, authorization via
 * Policies/Gates, never hard-coded in a controller):
 *
 *   - viewAny/view gated by a `view_*` permission
 *   - create/update/delete gated by matching `create_*`/`edit_*`/`delete_*`
 *   - Super Admin's blanket bypass lives once, centrally, in
 *     AuthServiceProvider::boot()'s Gate::before — no policy re-implements it.
 *
 * Sub-actions on a project (adding a milestone/task/team member) reuse
 * `update` rather than inventing new abilities, the same way Lead's
 * follow-up logging reuses LeadPolicy::update() — see ProjectMilestonePolicy
 * / ProjectTaskPolicy, which mirror it one level down.
 */
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_projects');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isStaff() && $user->can('view_projects');
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
