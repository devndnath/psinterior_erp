<?php

namespace Database\Seeders;

use App\Enums\LeadSource as LeadSourceEnum;
use App\Models\LeadSource;
use Illuminate\Database\Seeder;

/**
 * Seeds the admin-editable `lead_sources` table from
 * App\Enums\LeadSource's starting values (the enum documents this is its
 * only purpose — the column itself is a nullable FK to this table, not
 * the enum, so staff can add/retire sources from Settings without a
 * deploy, per ARCHITECTURE.md §6).
 */
class LeadSourceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (LeadSourceEnum::cases() as $case) {
            LeadSource::firstOrCreate(
                ['name' => $case->label()],
                ['is_active' => true],
            );
        }
    }
}
