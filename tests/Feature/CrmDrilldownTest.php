<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Livewire\Calls\Index as CallIndex;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Invoices\Index as InvoiceIndex;
use App\Livewire\Leads\Index as LeadIndex;
use App\Livewire\SiteVisits\Index as SiteVisitIndex;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\SiteVisit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Every figure on the CRM overview links to the list behind it; these
 * check the links are there and that each list filters to match.
 */
class CrmDrilldownTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_overview_figures_link_to_their_lists(): void
    {
        Lead::factory()->create(['status' => LeadStatus::New->value]);
        \App\Models\Project::factory()->create(['status' => 'planning']);

        $html = $this->actingAs($this->admin)->get(route('crm.overview'))->assertOk()->getContent();
        $today = today()->toDateString();

        foreach ([
            route('leads.index', ['status' => 'open']),
            route('leads.index', ['status' => 'new']),
            route('calls.index', ['when' => 'overdue']),
            route('calls.index', ['when' => 'week']),
            route('site-visits.index', ['from' => $today, 'to' => $today]),
            route('meetings.index', ['from' => $today, 'to' => $today]),
            route('projects.index', ['status' => 'planning']),
            route('invoices.index', ['due' => 'open']),
        ] as $url) {
            $this->assertStringContainsString(e($url), $html, $url);
        }
    }

    public function test_leads_filter_to_open_and_by_date(): void
    {
        Lead::factory()->create(['name' => 'Open One', 'status' => LeadStatus::Contacted->value, 'created_at' => now()->subMonths(2)]);
        Lead::factory()->create(['name' => 'Lost One', 'status' => LeadStatus::Lost->value]);
        Lead::factory()->create(['name' => 'Fresh One', 'status' => LeadStatus::New->value]);

        Livewire::actingAs($this->admin)->withQueryParams(['status' => 'open'])->test(LeadIndex::class)
            ->assertSee('Open One')->assertSee('Fresh One')->assertDontSee('Lost One');

        Livewire::actingAs($this->admin)->withQueryParams(['from' => today()->startOfMonth()->toDateString()])->test(LeadIndex::class)
            ->assertSee('Fresh One')->assertDontSee('Open One');
    }

    public function test_clients_and_site_visits_filter_by_date(): void
    {
        Client::factory()->create(['name' => 'Old Client', 'created_at' => now()->subYear()]);
        Client::factory()->create(['name' => 'New Client']);
        Livewire::actingAs($this->admin)->withQueryParams(['from' => today()->startOfMonth()->toDateString(), 'to' => today()->endOfMonth()->toDateString()])
            ->test(ClientIndex::class)->assertSee('New Client')->assertDontSee('Old Client');

        SiteVisit::factory()->create(['visit_date' => today()->toDateString(), 'purpose' => 'Visit today']);
        SiteVisit::factory()->create(['visit_date' => today()->addDays(20)->toDateString(), 'purpose' => 'Visit later']);
        $today = today()->toDateString();
        Livewire::actingAs($this->admin)->withQueryParams(['from' => $today, 'to' => $today])->test(SiteVisitIndex::class)
            ->assertViewHas('visits', fn ($v) => $v->total() === 1);
    }

    public function test_invoices_filter_to_outstanding_and_overdue(): void
    {
        Invoice::factory()->create(['invoice_no' => 'INV-LATE01', 'status' => 'issued', 'due_date' => today()->subDays(5)]);
        Invoice::factory()->create(['invoice_no' => 'INV-OPEN01', 'status' => 'issued', 'due_date' => today()->addDays(5)]);
        Invoice::factory()->create(['invoice_no' => 'INV-PAID01', 'status' => 'paid', 'due_date' => today()->subDays(5)]);

        Livewire::actingAs($this->admin)->withQueryParams(['due' => 'open'])->test(InvoiceIndex::class)
            ->assertSee('INV-LATE01')->assertSee('INV-OPEN01')->assertDontSee('INV-PAID01');
        Livewire::actingAs($this->admin)->withQueryParams(['due' => 'overdue'])->test(InvoiceIndex::class)
            ->assertSee('INV-LATE01')->assertDontSee('INV-OPEN01')->assertDontSee('INV-PAID01');
    }

    public function test_calls_next_7_and_30_days(): void
    {
        $client = Client::factory()->create();
        $client->followups()->create(['date' => today()->addDays(3)->toDateString(), 'type' => 'call', 'notes' => 'In three days']);
        $client->followups()->create(['date' => today()->addDays(20)->toDateString(), 'type' => 'call', 'notes' => 'In twenty days']);

        Livewire::actingAs($this->admin)->withQueryParams(['when' => 'week'])->test(CallIndex::class)
            ->assertSee('In three days')->assertDontSee('In twenty days');
        Livewire::actingAs($this->admin)->withQueryParams(['when' => 'month'])->test(CallIndex::class)
            ->assertSee('In three days')->assertSee('In twenty days');
    }
}
