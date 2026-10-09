<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Livewire\Leads\Form as LeadForm;
use App\Livewire\Leads\Index as LeadIndex;
use App\Livewire\Leads\Show as LeadShow;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_leads', 'create_leads', 'edit_leads', 'delete_leads', 'create_clients']);
    }

    public function test_a_user_without_view_permission_cannot_see_the_lead_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(LeadIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_lead_assigns_a_sequential_lead_number(): void
    {
        $source = LeadSource::factory()->create();

        Livewire::actingAs($this->staff)->test(LeadForm::class)
            ->set('name', 'Jane Homeowner')
            ->set('phone', '555-0100')
            ->set('lead_source_id', (string) $source->id)
            ->call('save')
            ->assertHasNoErrors();

        $lead = Lead::first();
        $this->assertNotNull($lead);
        $this->assertStringStartsWith('LEAD-', $lead->lead_no);
        $this->assertSame('Jane Homeowner', $lead->name);
    }

    public function test_name_is_required_to_create_a_lead(): void
    {
        Livewire::actingAs($this->staff)->test(LeadForm::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_logging_a_followup_on_a_new_lead_moves_it_to_follow_up_status(): void
    {
        $lead = Lead::factory()->create(['status' => LeadStatus::New->value, 'assigned_to' => null]);

        Livewire::actingAs($this->staff)->test(LeadShow::class, ['lead' => $lead])
            ->set('followup_notes', 'Called, interested in a quote.')
            ->call('addFollowup')
            ->assertHasNoErrors();

        $this->assertSame(LeadStatus::FollowUp, $lead->fresh()->status);
        $this->assertCount(1, $lead->followups()->get());
    }

    public function test_converting_a_won_lead_creates_a_client_and_links_it_back(): void
    {
        $lead = Lead::factory()->create(['status' => LeadStatus::Won->value]);

        Livewire::actingAs($this->staff)->test(LeadShow::class, ['lead' => $lead])
            ->call('convertToClient')
            ->assertRedirect();

        $lead->refresh();
        $this->assertTrue($lead->isConverted());
        $this->assertSame($lead->name, $lead->convertedClient->name);
    }

    public function test_converting_a_lead_that_is_not_won_fails(): void
    {
        $lead = Lead::factory()->create(['status' => LeadStatus::New->value]);

        Livewire::actingAs($this->staff)->test(LeadShow::class, ['lead' => $lead])
            ->call('convertToClient')
            ->assertHasErrors('status');

        $this->assertFalse($lead->fresh()->isConverted());
    }

    public function test_converting_requires_both_edit_leads_and_create_clients_permissions(): void
    {
        $limited = User::factory()->create();
        $limited->givePermissionTo(['view_leads', 'edit_leads']); // no create_clients

        $lead = Lead::factory()->create(['status' => LeadStatus::Won->value]);

        Livewire::actingAs($limited)->test(LeadShow::class, ['lead' => $lead])
            ->call('convertToClient')
            ->assertForbidden();
    }

    public function test_deleting_a_lead_requires_delete_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_leads');

        $lead = Lead::factory()->create();

        Livewire::actingAs($viewer)->test(LeadIndex::class)
            ->call('deleteLead', $lead->id)
            ->assertForbidden();

        $this->assertNotNull($lead->fresh());
    }

    public function test_lead_list_can_be_filtered_by_status(): void
    {
        Lead::factory()->create(['status' => LeadStatus::Won->value, 'name' => 'Won Lead']);
        Lead::factory()->create(['status' => LeadStatus::New->value, 'name' => 'New Lead']);

        Livewire::actingAs($this->staff)->test(LeadIndex::class)
            ->set('status', LeadStatus::Won->value)
            ->assertSee('Won Lead')
            ->assertDontSee('New Lead');
    }
}
