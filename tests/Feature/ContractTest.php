<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\QuotationStatus;
use App\Livewire\Contracts\Form as ContractForm;
use App\Livewire\Contracts\Index as ContractIndex;
use App\Livewire\Contracts\Show as ContractShow;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\User;
use App\Services\ContractService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_contracts', 'create_contracts', 'edit_contracts', 'delete_contracts']);
    }

    public function test_a_user_without_view_permission_cannot_see_the_contract_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ContractIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_contract_assigns_a_sequential_number(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(ContractForm::class)
            ->set('project_id', (string) $project->id)
            ->set('amount', '500000')
            ->set('items.0.description', 'Full interior fit-out')
            ->set('items.0.amount', '500000')
            ->call('save')
            ->assertHasNoErrors();

        $contract = Contract::first();
        $this->assertNotNull($contract);
        $this->assertStringStartsWith('CON-', $contract->contract_no);
        $this->assertSame(ContractStatus::Draft, $contract->status);
        $this->assertSame($project->client_id, $contract->client_id);
    }

    public function test_creating_a_contract_from_an_approved_quotation_copies_its_amount_and_items(): void
    {
        $project = Project::factory()->create();
        $quotation = Quotation::factory()->for($project)->create([
            'client_id' => $project->client_id,
            'status' => QuotationStatus::Approved->value,
            'grand_total' => 750000,
        ]);
        $quotation->items()->create(['type' => 'item', 'description' => 'Kitchen fit-out', 'qty' => 1, 'rate' => 750000, 'amount' => 750000]);

        $contract = app(ContractService::class)->createFromQuotation($quotation);

        $this->assertEquals(750000, $contract->amount);
        $this->assertSame($quotation->id, $contract->quotation_id);
        $this->assertSame(1, $contract->items()->count());
    }

    public function test_activating_a_contract_updates_the_projects_contract_value_and_supersedes_other_active_contracts(): void
    {
        $project = Project::factory()->create(['contract_value' => 0]);
        $oldActive = Contract::factory()->for($project)->create(['status' => ContractStatus::Active->value, 'amount' => 100000]);
        $newContract = Contract::factory()->for($project)->create(['status' => ContractStatus::Draft->value, 'amount' => 600000]);

        Livewire::actingAs($this->staff)->test(ContractShow::class, ['contract' => $newContract])
            ->call('activate');

        $this->assertSame(ContractStatus::Completed, $oldActive->fresh()->status);
        $this->assertSame(ContractStatus::Active, $newContract->fresh()->status);
        $this->assertEquals(600000, $project->fresh()->contract_value);
    }

    public function test_deleting_a_contract_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_contracts');

        $project = Project::factory()->create();
        $contract = Contract::factory()->for($project)->create();

        Livewire::actingAs($viewer)->test(ContractIndex::class)
            ->call('deleteContract', $contract->id)
            ->assertForbidden();

        $this->assertNotNull($contract->fresh());
    }
}
