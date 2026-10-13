<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Owns creating/editing a staff (`user_type = Staff`) login and syncing
 * its Spatie roles — the mechanism that previously only existed as
 * database/seeders/SuperAdminSeeder.php or raw tinker access (the audit
 * gap this phase closes). Both the user row and its role assignment are
 * wrapped in one transaction, matching the service-per-aggregate /
 * DB::transaction() convention used across every other multi-row write
 * in this app.
 */
class UserService
{
    /**
     * @param  array{name:string,email:string,phone:?string,password:?string,is_active:bool}  $data
     * @param  array<int,string>  $roles  Spatie role names (guard `web`)
     */
    public function create(array $data, array $roles): User
    {
        return DB::transaction(function () use ($data, $roles) {
            $user = User::create([
                'company_id' => auth()->user()?->company_id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'user_type' => UserType::Staff,
                'is_active' => $data['is_active'] ?? true,
                'email_verified_at' => now(),
            ]);

            $user->syncRoles($roles);

            return $user;
        });
    }

    /**
     * @param  array{name:string,email:string,phone:?string,password:?string,is_active:bool}  $data
     * @param  array<int,string>  $roles
     */
    public function update(User $user, array $data, array $roles): User
    {
        return DB::transaction(function () use ($user, $data, $roles) {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ];

            // Password is optional on edit — an empty value means
            // "leave it unchanged", never "set it to a blank hash".
            if (! empty($data['password'])) {
                $attributes['password'] = Hash::make($data['password']);
            }

            $user->update($attributes);
            $user->syncRoles($roles);

            return $user;
        });
    }
}
