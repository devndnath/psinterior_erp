<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Owns creating/editing a staff (`web` guard) role and its permission
 * set — the mechanism that previously only existed as hand-edited PHP
 * in database/seeders/RolePermissionSeeder.php, re-run from the
 * command line. That seeder is still what provisions the 11 default
 * roles on a fresh install; this service lets Admin/Super Admin create
 * additional roles or adjust permissions afterwards without touching
 * code.
 */
class RoleService
{
    /**
     * @param  array<int,string>  $permissions  permission names (guard `web`)
     */
    public function create(string $name, array $permissions): Role
    {
        return DB::transaction(function () use ($name, $permissions) {
            $role = Role::create(['name' => $name, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);

            return $role;
        });
    }

    /**
     * @param  array<int,string>  $permissions
     */
    public function update(Role $role, string $name, array $permissions): Role
    {
        return DB::transaction(function () use ($role, $name, $permissions) {
            $role->update(['name' => $name]);
            $role->syncPermissions($permissions);

            return $role;
        });
    }
}
