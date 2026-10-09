<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        $user = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'admin@interior-erp.test'],
            [
                'company_id' => $company?->id,
                'name' => 'Super Admin',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'password')),
                'user_type' => UserType::Staff,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles(['super-admin']);

        $this->command?->warn(
            'Super admin seeded: admin@interior-erp.test / '.env('SUPER_ADMIN_PASSWORD', 'password').
            ' — change this password immediately in a non-local environment.'
        );
    }
}
