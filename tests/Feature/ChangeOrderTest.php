<?php

namespace Tests\Feature;

use App\Enums\ApprovalDecision;
use App\Enums\ChangeOrderStatus;
use App\Livewire\ChangeOrders\Form as ChangeOrderForm;
use App\Livewire\ChangeOrders\Index as ChangeOrderIndex;
use App\Livewire\ChangeOrders\Show as ChangeOrderShow;
use App\Models\ChangeOrder;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChangeOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_change_orders', 'create_change_orders', 'edit_change_orders', 'delete_change_orders',
            'approve_change_order',
        ]);
    }

    public function test_a_user_without_view_permission_cannot_see_the_change_order_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ChangeOrderIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_change_order_assigns_a_sequential_number_and_sums_item_cost(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(ChangeOrderForm::class)
            ->set('project_id', (string) $project->id)
            ->set('description', 'Additional false ceiling work')
            ->set('items.0.description', 'Extra false ceiling')
            ->set('items.0.qty', '20')
            ->set('items.0.rate', '500')
            ->call('save')
            ->assertHasNoErrors();

        $changeOrder = ChangeOrder::first();
        $this->assertNotNull($changeOrder);
        $this->assertStringStartsWith('CO-', $changeOrder->co_no);
        $this->assertSame(ChangeOrderStatus::Pending, $changeOrder->status);
        $this->assertEquals(10000, $changeOrder->cost);
    }

    public function test_approving_a_change_order_adds_its_cost_to_the_projects_contract_value(): void
    {
        $project = Project::factory()->create(['contract_value' => 500000]);
        $changeOrder = ChangeOrder::factory()->for($project)->create(['cost' => 25000, 'status' => ChangeOrderStatus::Pending->value]);

        Livewire::actingAs($this->staff)->test(ChangeOrderShow::class, ['changeOrder' => $changeOrder])
            ->call('toggleDecisionForm')
            ->set('decision', ApprovalDecision::Approved->value)
            ->call('recordDecision')
            ->assertHasNoErrors();

        $fresh = $changeOrder->fresh();
        $this->assertSame(ChangeOrderStatus::Approved, $fresh->status);
        $this->assertSame(ApprovalDecision::Approved, $fresh->client_approval_status);
        $this->assertNotNull($fresh->approved_at);
        $this->assertEquals(525000, $project->fresh()->contract_value);
    }

    public function test_rejecting_a_change_order_does_not_change_the_projects_contract_value(): void
    {
        $project = Project::factory()->create(['contract_value' => 500000]);
        $changeOrder = ChangeOrder::factory()->for($project)->create(['cost' => 25000, 'status' => ChangeOrderStatus::Pending->value]);

        Livewire::actingAs($this->staff)->test(ChangeOrderShow::class, ['changeOrder' => $changeOrder])
            ->call('toggleDecisionForm')
            ->set('decision', ApprovalDecision::Rejected->value)
            ->call('recordDecision')
            ->assertHasNoErrors();

        $this->assertSame(ChangeOrderStatus::Rejected, $changeOrder->fresh()->status);
        $this->assertEquals(500000, $project->fresh()->contract_value);
    }

    public function test_recording_decision_requires_the_approve_change_order_ability_distinct_from_edit(): void
    {
        $editorOnly = User::factory()->create();
        $editorOnly->givePermissionTo(['view_change_orders', 'edit_change_orders']);

        $project = Project::factory()->create();
        $changeOrder = ChangeOrder::factory()->for($project)->create();

        Livewire::actingAs($editorOnly)->test(ChangeOrderShow::class, ['changeOrder' => $changeOrder])
            ->call('toggleDecisionForm')
            ->assertForbidden();
    }

    public function test_deleting_a_change_order_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_change_orders');

        $project = Project::factory()->create();
        $changeOrder = ChangeOrder::factory()->for($project)->create();

        Livewire::actingAs($viewer)->test(ChangeOrderIndex::class)
            ->call('deleteChangeOrder', $changeOrder->id)
            ->assertForbidden();

        $this->assertNotNull($changeOrder->fresh());
    }
}
