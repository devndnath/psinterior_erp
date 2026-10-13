<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Phase 1: company, roles/permissions, settings, super admin.
     * Phase 2 adds LeadSourceSeeder (the lead_sources table's starting
     * rows). `DemoSeeder` (spec §65 — one interlinked sample record
     * through nearly every module) now exists but is deliberately NOT
     * called here — it's opt-in via `php artisan db:seed
     * --class=DemoSeeder` (see README.md §5), since a production
     * install shouldn't get fictional demo clients/projects for free.
     */
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            SettingsSeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            LeadSourceSeeder::class,
        ]);
    }
}
