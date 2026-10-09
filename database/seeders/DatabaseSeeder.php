<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Phase 1: company, roles/permissions, settings, super admin.
     * Phase 2 adds LeadSourceSeeder (the lead_sources table's starting
     * rows). DemoSeeder (spec §65 — demo clients/projects/products/
     * vendors/quotations/invoices/expenses) is added once enough modules
     * exist to make a convincing demo dataset.
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
