<?php

namespace Tests\Feature;

use App\Enums\ApprovalDecision;
use App\Enums\QuotationStatus;
use App\Livewire\Quotations\Form as QuotationForm;
use App\Livewire\Quotations\Index as QuotationIndex;
use App\Livewire\Quotations\Show as QuotationShow;
use App\Models\Boq;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_quotations', 'create_quotations', 'edit_quotations', 'delete_quotations',
            'approve_quotation',
        ]);
    }

    public function test_a_user_without_view_permission_cannot_see_the_quotation_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(QuotationIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_quotation_assigns_a_sequential_number_and_computes_totals(): void
    {
        $project = Project::factory()->create();

        Livewire::actingAs($this->staff)->test(QuotationForm::class)
            ->set('project_id', (string) $project->id)
            ->set('discount', '0')
            ->set('tax', '0')
            ->set('items.0.description', 'Living room furniture package')
            ->set('items.0.qty', '2')
            ->set('items.0.rate', '15000')
            ->call('save')
            ->assertHasNoErrors();

        $quotation = Quotation::first();
        $this->assertNotNull($quotation);
        $this->assertStringStartsWith('QUO-', $quotation->quotation_no);
        $this->assertSame(1, $quotation->version);
        $this->assertSame(QuotationStatus::Draft, $quotation->status);
        $this->assertEquals(30000, $quotation->grand_total);
        $this->assertSame($project->client_id, $quotation->client_id);
    }

    /**
     * QuotationForm::mount() reads the `?boq=` query string directly via
     * request()->query() to build the same copy-from-BOQ prefill that a
     * "Create quotation from this BOQ" link on the BOQ Show page sends
     * (not easily driven through Livewire::test, which has no request()
     * to spoof) — so this exercises QuotationService::create()'s
     * from-BOQ branch directly, the same code path the form calls into.
     */
    public function test_creating_a_quotation_from_a_boq_copies_its_items(): void
    {
        $project = Project::factory()->create();
        $boq = Boq::factory()->for($project)->create();
        $boq->items()->create(['item' => 'Wardrobe', 'qty' => 1, 'rate' => 50000, 'amount' => 50000]);

        $quotation = app(\App\Services\QuotationService::class)->create(
            $project,
            ['discount' => 0, 'tax' => 0, 'client_id' => $project->client_id],
            [],
            $boq,
        );

        $this->assertSame(1, $quotation->items()->count());
        $this->assertEquals(50000, $quotation->grand_total);
        $this->assertSame($boq->id, $quotation->boq_id);
    }

    public function test_editing_a_quotation_that_has_left_draft_snapshots_the_previous_version(): void
    {
        $project = Project::factory()->create();
        $quotation = Quotation::factory()->for($project)->create(['status' => QuotationStatus::Sent->value]);
        $quotation->items()->create(['type' => 'item', 'description' => 'Original item', 'qty' => 1, 'rate' => 1000, 'amount' => 1000]);
        app(\App\Services\QuotationService::class)->recalculateTotals($quotation);

        Livewire::actingAs($this->staff)->test(QuotationForm::class, ['quotation' => $quotation])
            ->set('items.0.description', 'Revised item')
            ->set('items.0.rate', '2000')
            ->call('save')
            ->assertHasNoErrors();

        $fresh = $quotation->fresh();
        $this->assertSame(2, $fresh->version);
        $this->assertSame(QuotationStatus::Draft, $fresh->status);
        $this->assertSame(1, $fresh->versions()->count());

        $snapshot = $fresh->versions()->first();
        $this->assertSame(1, $snapshot->version);
        $this->assertSame('sent', $snapshot->snapshot['status']);
    }

    public function test_recording_an_approved_decision_moves_the_quotation_to_approved(): void
    {
        $project = Project::factory()->create();
        $quotation = Quotation::factory()->for($project)->create(['status' => QuotationStatus::Negotiation->value]);

        Livewire::actingAs($this->staff)->test(QuotationShow::class, ['quotation' => $quotation])
            ->call('toggleDecisionForm')
            ->set('decision', ApprovalDecision::Approved->value)
            ->call('recordDecision')
            ->assertHasNoErrors();

        $this->assertSame(QuotationStatus::Approved, $quotation->fresh()->status);
    }

    public function test_recording_decision_requires_the_approve_quotation_ability_distinct_from_edit(): void
    {
        $editorOnly = User::factory()->create();
        $editorOnly->givePermissionTo(['view_quotations', 'edit_quotations']);

        $project = Project::factory()->create();
        $quotation = Quotation::factory()->for($project)->create(['status' => QuotationStatus::Sent->value]);

        Livewire::actingAs($editorOnly)->test(QuotationShow::class, ['quotation' => $quotation])
            ->call('toggleDecisionForm')
            ->assertForbidden();
    }

    public function test_deleting_a_quotation_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_quotations');

        $project = Project::factory()->create();
        $quotation = Quotation::factory()->for($project)->create();

        Livewire::actingAs($viewer)->test(QuotationIndex::class)
            ->call('deleteQuotation', $quotation->id)
            ->assertForbidden();

        $this->assertNotNull($quotation->fresh());
    }
}
