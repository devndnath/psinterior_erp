<?php

namespace Tests\Feature;

use App\Enums\FollowupStatus;
use App\Enums\LeadStatus;
use App\Livewire\Calls\Index as CallIndex;
use App\Livewire\Calls\Panel as CallPanel;
use App\Livewire\Crm\Overview;
use App\Models\Client;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\User;
use App\Services\LeadService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Calls for leads and clients: one log (lead_followups) behind the lead
 * and client call cards, the Calls page, the CRM overview and the
 * calendar.
 */
class CallsTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_leads', 'edit_leads', 'view_clients', 'edit_clients', 'create_clients']);
    }

    public function test_a_call_can_be_scheduled_for_a_client_from_the_client_page(): void
    {
        $client = Client::factory()->create(['phone' => '01711000000']);

        Livewire::actingAs($this->staff)->test(CallPanel::class, ['client' => $client])
            ->call('openForm', 'pending')
            ->set('date', now()->toDateString())
            ->set('notes', 'Discuss the second installment.')
            ->call('save')
            ->assertHasNoErrors();

        $call = LeadFollowup::sole();
        $this->assertSame($client->id, $call->client_id);
        $this->assertNull($call->lead_id);
        $this->assertSame(FollowupStatus::Pending, $call->status);
    }

    public function test_client_calls_show_on_the_calls_page_and_the_crm_overview(): void
    {
        $client = Client::factory()->create(['name' => 'Rahim Uddin']);
        $lead = Lead::factory()->create(['name' => 'Karim Lead', 'status' => LeadStatus::FollowUp->value]);
        $client->followups()->create(['date' => now()->toDateString(), 'type' => 'call']);
        $lead->followups()->create(['date' => now()->subDay()->toDateString(), 'type' => 'whatsapp']);

        Livewire::actingAs($this->staff)->test(CallIndex::class)
            ->assertSee('Rahim Uddin')->assertSee('Karim Lead')
            ->assertViewHas('counts', fn ($c) => $c['today'] === 1 && $c['overdue'] === 1)
            ->set('with', 'client')
            ->assertSee('Rahim Uddin')->assertDontSee('Karim Lead');

        $calls = Livewire::actingAs($this->staff)->test(Overview::class)->viewData('calls');
        $this->assertSame(1, $calls['counts']['today']);
        $this->assertSame(1, $calls['counts']['overdue']);
        $this->assertCount(2, $calls['due']);
    }

    public function test_logging_a_call_for_a_client_from_the_calls_page(): void
    {
        $client = Client::factory()->create();

        Livewire::actingAs($this->staff)->test(CallIndex::class)
            ->call('openForm', 'done')
            ->set('contact', 'client:'.$client->id)
            ->set('outcome', 'interested')
            ->set('next_date', now()->addDays(3)->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        // The finished call, plus the next call it booked.
        $calls = $client->followups()->get();
        $this->assertCount(2, $calls);
        $this->assertSame(1, $calls->where('status', FollowupStatus::Done)->count());
        $this->assertSame(now()->addDays(3)->toDateString(), $calls->firstWhere('status', FollowupStatus::Pending)->date->toDateString());
    }

    public function test_finishing_a_call_records_the_outcome_and_can_book_the_next_one(): void
    {
        $client = Client::factory()->create();
        $call = $client->followups()->create(['date' => now()->toDateString(), 'type' => 'call']);

        Livewire::actingAs($this->staff)->test(CallIndex::class)
            ->call('startComplete', $call->id)
            ->set('complete_outcome', 'no_answer')
            ->set('complete_next_date', now()->addDay()->toDateString())
            ->call('complete')
            ->assertHasNoErrors();

        $call->refresh();
        $this->assertSame(FollowupStatus::Missed, $call->status); // nobody picked up
        $this->assertSame(2, $client->followups()->count());
        $this->assertSame(1, $client->followups()->where('status', 'pending')->count());
    }

    public function test_a_converted_leads_calls_follow_it_to_the_client(): void
    {
        $lead = Lead::factory()->create(['status' => LeadStatus::Won->value]);
        $lead->followups()->create(['date' => now()->subDays(5)->toDateString(), 'type' => 'call', 'status' => 'done']);
        $lead->followups()->create(['date' => now()->addDay()->toDateString(), 'type' => 'call']);

        $client = app(LeadService::class)->convertToClient($lead);

        $this->assertSame(2, $client->followups()->count());
        // The scheduled one still counts as a call to make (the lead itself is closed).
        $this->assertSame(1, LeadFollowup::toMake()->count());

        Livewire::actingAs($this->staff)->test(CallPanel::class, ['client' => $client])
            ->assertSee('from lead '.$lead->lead_no);
    }

    public function test_a_user_who_can_only_see_leads_does_not_see_client_calls(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_leads');
        Client::factory()->create(['name' => 'Hidden Client'])->followups()->create(['date' => now()->toDateString(), 'type' => 'call']);

        Livewire::actingAs($user)->test(CallIndex::class)
            ->assertOk()->assertDontSee('Hidden Client');
    }

    public function test_calls_page_needs_lead_or_client_access(): void
    {
        $this->actingAs(User::factory()->create())->get(route('calls.index'))->assertForbidden();

        $viewer = User::factory()->create();
        $viewer->givePermissionTo(['view_clients']);
        $this->actingAs($viewer)->get(route('calls.index'))->assertOk();
    }

    public function test_logging_a_call_needs_edit_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo(['view_clients']);
        $client = Client::factory()->create();

        Livewire::actingAs($viewer)->test(CallPanel::class, ['client' => $client])
            ->call('save')
            ->assertForbidden();
        $this->assertSame(0, LeadFollowup::count());
    }

    public function test_pages_with_calls_render(): void
    {
        $client = Client::factory()->create(['phone' => '01711000000']);
        $lead = Lead::factory()->create(['status' => LeadStatus::FollowUp->value, 'phone' => '01811000000']);
        $client->followups()->create(['date' => now()->toDateString(), 'type' => 'call', 'notes' => 'Client call note']);
        $lead->followups()->create(['date' => now()->toDateString(), 'type' => 'whatsapp', 'notes' => 'Lead call note']);
        $this->staff->givePermissionTo(['view_meetings']);

        $this->actingAs($this->staff);
        $this->get(route('calls.index'))->assertOk()->assertSee('Client call note')->assertSee('https://wa.me/8801711000000', false);
        $this->get(route('clients.show', $client))->assertOk()->assertSee('Calls &amp; follow-ups', false)->assertSee('Client call note');
        $this->get(route('leads.show', $lead))->assertOk()->assertSee('Lead call note')->assertSee('tel:01811000000', false);
        $this->get(route('meetings.index'))->assertOk()->assertSee(route('calls.index'));
        $this->get(route('calendar.index'))->assertOk()->assertSee('WhatsApp · '.$lead->name);
    }
}
