<?php

namespace Tests\Feature;

use App\Livewire\Warranties\Index as WarrantyIndex;
use App\Models\Project;
use App\Models\User;
use App\Models\Warranty;
use App\Services\WarrantyService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WarrantyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_a_user_without_view_warranties_permission_cannot_see_the_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(WarrantyIndex::class)
            ->assertForbidden();
    }

    public function test_is_active_is_true_within_the_date_range(): void
    {
        $warranty = Warranty::factory()->create([
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->assertTrue($warranty->isActive());
    }

    public function test_is_active_is_false_once_expired(): void
    {
        $warranty = Warranty::factory()->create([
            'start_date' => now()->subDays(100)->toDateString(),
            'end_date' => now()->subDays(10)->toDateString(),
        ]);

        $this->assertFalse($warranty->isActive());
    }

    public function test_is_currently_under_warranty_checks_all_the_projects_warranties(): void
    {
        $project = Project::factory()->create();

        Warranty::factory()->create([
            'project_id' => $project->id,
            'start_date' => now()->subDays(100)->toDateString(),
            'end_date' => now()->subDays(50)->toDateString(),
        ]);

        $this->assertFalse(app(WarrantyService::class)->isCurrentlyUnderWarranty($project->fresh()));

        Warranty::factory()->create([
            'project_id' => $project->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->assertTrue(app(WarrantyService::class)->isCurrentlyUnderWarranty($project->fresh()));
    }
}
