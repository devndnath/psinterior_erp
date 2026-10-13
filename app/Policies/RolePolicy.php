<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Lets Admin/Super Admin manage roles and their permission sets through
 * a screen instead of hand-editing database/seeders/RolePermissionSeeder.php
 * and re-running it. Spatie's own Role model (guard `web`) is used
 * directly — there is no app-owned Role model to wrap it in, same as
 * how VendorInvoicePolicy etc. gate on an existing Eloquent model.
 *
 * Uses the `roles` CRUD permission set RolePermissionSeeder has
 * generated since Phase 1 ($crudModules includes 'roles') but, like
 * `users`, no role definition granted explicitly — only Admin (all
 * permissions except view_all_companies) and Super Admin (Gate::before
 * bypass) can reach this by default.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->isStaff() && $user->can('view_roles');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_roles');
    }

    public function update(User $user, Role $role): bool
    {
        // super-admin bypasses every permission check by design
        // (AuthServiceProvider's Gate::before) — editing its permission
        // list here would be misleading since it has no effect, so it's
        // blocked outright rather than silently doing nothing useful.
        if ($role->name === 'super-admin') {
            return false;
        }

        return $user->isStaff() && $user->can('edit_roles');
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->name === 'super-admin') {
            return false;
        }

        // A role with users still assigned can't be deleted out from
        // under them — reassign or remove those users from the role
        // first.
        if ($role->users()->count() > 0) {
            return false;
        }

        return $user->isStaff() && $user->can('delete_roles');
    }
}
