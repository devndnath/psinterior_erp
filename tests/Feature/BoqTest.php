<?php

namespace Tests\Feature;

use App\Enums\BoqStatus;
use App\Livewire\Boqs\Form as BoqForm;
use App\Livewire\Boqs\Index as BoqIndex;
use App\Livewire\Boqs\Show as BoqShow;
use App\Models\Boq;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BoqTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_boqs', 'create_boqs', 'edit_boqs', 'delete_boqs']);
    }

    public function test_a_user_without_view_permission_cannot_see_the_boq_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(BoqIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_boq_assigns_a_sequential_number_and_computes_totals(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(BoqForm::class)
            ->set('project_id', (string) $project->id)
            ->set('discount', '100')
            ->set('tax', '50')
            ->set('items.0.item', 'False ceiling')
            ->set('items.0.qty', '10')
            ->set('items.0.rate', '200')
            ->call('save')
            ->assertHasNoErrors();

        $boq = Boq::first();
        $this->assertNotNull($boq);
        $this->assertStringStartsWith('BOQ-', $boq->boq_no);
        $this->assertSame(1, $boq->version);
        $this->assertSame(BoqStatus::Draft, $boq->status);
        $this->assertEquals(2000, $boq->subtotal);
        $this->assertEquals(1950, $boq->grand_total); // 2000 - 100 discount + 50 tax
    }

    public function test_creating_a_new_version_supersedes_the_previously_active_boq(): void
    {
        $project = Project::factory()->create();
        $first = Boq::factory()->for($project)->create(['status' => BoqStatus::Active->value, 'version' => 1]);

        Livewire::actingAs($this->staff)->test(BoqShow::class, ['boq' => $first])
            ->call('createNewVersion');

        $this->assertSame(BoqStatus::Superseded, $first->fresh()->status);

        $second = Boq::where('project_id', $project->id)->where('version', 2)->first();
        $this->assertNotNull($second);
        $this->assertSame(BoqStatus::Draft, $second->status);
    }

    public function test_activating_a_boq_supersedes_any_other_active_boq_on_the_same_project(): void
    {
        $project = Project::factory()->create();
        $activeOne = Boq::factory()->for($project)->create(['status' => BoqStatus::Active->value, 'version' => 1]);
        $draftTwo = Boq::factory()->for($project)->create(['status' => BoqStatus::Draft->value, 'version' => 2]);

        Livewire::actingAs($this->staff)->test(BoqShow::class, ['boq' => $draftTwo])
            ->call('activate');

        $this->assertSame(BoqStatus::Superseded, $activeOne->fresh()->status);
        $this->assertSame(BoqStatus::Active, $draftTwo->fresh()->status);
    }

    public function test_deleting_a_boq_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_boqs');

        $project = Project::factory()->create();
        $boq = Boq::factory()->for($project)->create();

        Livewire::actingAs($viewer)->test(BoqIndex::class)
            ->call('deleteBoq', $boq->id)
            ->assertForbidden();

        $this->assertNotNull($boq->fresh());
    }
}
