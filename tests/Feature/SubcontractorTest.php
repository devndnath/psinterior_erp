<?php

namespace Tests\Feature;

use App\Livewire\Subcontractors\Index as SubcontractorIndex;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\SubcontractorAssignment;
use App\Models\User;
use App\Services\SubcontractorAssignmentService;
use App\Services\SubcontractorPaymentService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubcontractorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_a_user_without_view_subcontractors_cannot_see_the_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(SubcontractorIndex::class)
            ->assertForbidden();
    }

    public function test_a_user_with_subcontractors_permission_can_see_the_list(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_subcontractors']);

        Livewire::actingAs($user)->test(SubcontractorIndex::class)
            ->assertSuccessful();
    }

    public function test_creating_an_assignment_against_a_project(): void
    {
        $project = Project::factory()->create();
        $subcontractor = Subcontractor::factory()->create();

        $assignment = app(SubcontractorAssignmentService::class)->create($project, [
            'subcontractor_id' => $subcontractor->id,
            'work_description' => 'False ceiling work',
            'rate' => 25000,
        ]);

        $this->assertSame($project->id, $assignment->project_id);
        $this->assertSame($subcontractor->id, $assignment->subcontractor_id);
        $this->assertSame(1, $project->subcontractorAssignments()->count());
    }

    public function test_update_progress_clamps_and_rejects_out_of_range_values(): void
    {
        $assignment = SubcontractorAssignment::factory()->create(['progress' => 10]);
        $service = app(SubcontractorAssignmentService::class);

        $updated = $service->updateProgress($assignment, 75);
        $this->assertSame(75, $updated->progress);

        $this->expectException(\InvalidArgumentException::class);
        $service->updateProgress($assignment, 150);
    }

    public function test_update_progress_rejects_negative_values(): void
    {
        $assignment = SubcontractorAssignment::factory()->create(['progress' => 10]);
        $service = app(SubcontractorAssignmentService::class);

        $this->expectException(\InvalidArgumentException::class);
        $service->updateProgress($assignment, -5);
    }

    public function test_total_paid_sums_payments_correctly(): void
    {
        $assignment = SubcontractorAssignment::factory()->create();
        $service = app(SubcontractorPaymentService::class);

        $service->create($assignment, ['date' => now()->toDateString(), 'amount' => 10000]);
        $service->create($assignment, ['date' => now()->toDateString(), 'amount' => 5000.50]);

        $this->assertEquals(15000.50, $assignment->totalPaid());
    }
}
