<?php

namespace Tests\Feature;

use App\Enums\InspectionResult;
use App\Livewire\Inspections\Form as InspectionForm;
use App\Livewire\Inspections\Index as InspectionIndex;
use App\Models\Inspection;
use App\Models\Project;
use App\Models\User;
use App\Services\InspectionService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InspectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_inspections', 'create_inspections', 'edit_inspections', 'delete_inspections']);
    }

    public function test_a_user_without_view_inspections_permission_cannot_see_the_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(InspectionIndex::class)
            ->assertForbidden();
    }

    public function test_overall_result_is_computed_as_the_worst_of_items_when_not_overridden(): void
    {
        $project = Project::factory()->create();

        $inspection = app(InspectionService::class)->create($project, [
            'inspection_date' => now()->toDateString(),
        ], [
            ['checklist_item' => 'Wiring', 'result' => InspectionResult::Passed->value],
            ['checklist_item' => 'Paint', 'result' => InspectionResult::RequiresCorrection->value],
            ['checklist_item' => 'Plumbing', 'result' => InspectionResult::Failed->value],
        ]);

        $this->assertSame(InspectionResult::Failed, $inspection->result);
    }

    public function test_overall_result_falls_back_to_requires_correction_when_no_item_failed(): void
    {
        $project = Project::factory()->create();

        $inspection = app(InspectionService::class)->create($project, [
            'inspection_date' => now()->toDateString(),
        ], [
            ['checklist_item' => 'Wiring', 'result' => InspectionResult::Passed->value],
            ['checklist_item' => 'Paint', 'result' => InspectionResult::RequiresCorrection->value],
        ]);

        $this->assertSame(InspectionResult::RequiresCorrection, $inspection->result);
    }

    public function test_an_explicit_result_override_always_wins_over_the_computed_one(): void
    {
        $project = Project::factory()->create();

        $inspection = app(InspectionService::class)->create($project, [
            'inspection_date' => now()->toDateString(),
            'result' => InspectionResult::Passed->value,
        ], [
            ['checklist_item' => 'Wiring', 'result' => InspectionResult::Failed->value],
        ]);

        $this->assertSame(InspectionResult::Passed, $inspection->result);
    }

    public function test_creating_an_inspection_via_the_form_persists_items(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(InspectionForm::class)
            ->set('project_id', (string) $project->id)
            ->set('inspection_date', now()->toDateString())
            ->set('items.0.checklist_item', 'Wiring')
            ->set('items.0.result', InspectionResult::Failed->value)
            ->call('save')
            ->assertHasNoErrors();

        $inspection = Inspection::first();
        $this->assertNotNull($inspection);
        $this->assertSame(InspectionResult::Failed, $inspection->result);
        $this->assertSame(1, $inspection->items()->count());
    }
}
