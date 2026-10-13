<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    /**
     * Creates and returns a login tied to an Employee/Client/Vendor
     * record, for the inline "create a login for this person" checkbox
     * on those three Forms (the audit gap those forms previously had:
     * Employee/Client/Vendor.user_id has existed since Phase 1 with no
     * UI that ever set it). Unlike create() above — which always makes
     * a Staff account via the Users screen with an admin-chosen password
     * — this generates the password itself and skips role syncing
     * entirely for Client/Vendor accounts, since a portal login never
     * holds Spatie roles (ARCHITECTURE.md §5/§7: their access is
     * ownership-based, decided in Policies, not by a permission list).
     *
     * There is no outbound email in this app (see
     * LeadConvertedNotification's docblock), so the generated password
     * is returned in plain text for the caller to show the admin
     * exactly once — it is never stored anywhere in plain text.
     *
     * @param  array<int,string>  $roles  ignored unless $type is Staff
     * @return array{user: User, password: string}
     */
    public function createLinkedAccount(string $name, string $email, UserType $type, array $roles = []): array
    {
        return DB::transaction(function () use ($name, $email, $type, $roles) {
            $password = Str::password(12);

            $user = User::create([
                'company_id' => auth()->user()?->company_id,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'user_type' => $type,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            if ($type === UserType::Staff && $roles) {
                $user->syncRoles($roles);
            }

            return ['user' => $user, 'password' => $password];
        });
    }
}
