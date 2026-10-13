<?php

namespace App\Policies;

use App\Models\User;

/**
 * Staff login management (spec gap closed in this phase — previously
 * the ONLY way a second staff account could exist was
 * database/seeders/SuperAdminSeeder.php or raw tinker access). Uses the
 * `users` CRUD permission set that RolePermissionSeeder has generated
 * since Phase 1 ($crudModules includes 'users') but that no role
 * definition granted until now except implicitly: `admin` = every
 * permission except `view_all_companies`, and `super-admin` bypasses
 * every Gate check outright (AuthServiceProvider's Gate::before). So by
 * default only Admin/Super Admin can reach this screen — deliberately,
 * since creating logins and assigning roles is a privileged action.
 *
 * Only ever manages `user_type = Staff` rows (User::scopeStaff) — a
 * Client or Vendor's login is a side effect of their own Client/Vendor
 * record, not something edited here (see User::client()/vendor()
 * docblocks and the Client/Vendor Form components).
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_users');
    }

    public function view(User $user, User $target): bool
    {
        return $user->isStaff() && $user->can('view_users');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_users');
    }

    public function update(User $user, User $target): bool
    {
        // A non-super-admin can never edit a super-admin's account
        // (role list, active flag, password) — super-admin itself
        // bypasses this Policy entirely via Gate::before, so this
        // branch only runs for everyone else.
        if ($target->hasRole('super-admin')) {
            return false;
        }

        return $user->isStaff() && $user->can('edit_users');
    }

    public function delete(User $user, User $target): bool
    {
        // Never let anyone delete their own login from this screen, or
        // delete a super-admin account.
        if ($user->is($target) || $target->hasRole('super-admin')) {
            return false;
        }

        return $user->isStaff() && $user->can('delete_users');
    }
}
