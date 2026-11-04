<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Livewire\Crm\Overview;
use App\Livewire\Dashboard\Index as Dashboard;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\SiteVisit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The CRM overview the dashboard's CRM card opens: call buckets from
 * pending call/WhatsApp follow-ups on open leads, visits and meetings
 * ahead, and sections hidden without their permission.
 */
class CrmOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function followup(Lead $lead, string $type, string $date, string $status = 'pending'): void
    {
        $lead->followups()->create(['date' => $date, 'type' => $type, 'status' => $status]);
    }

    public function test_call_buckets_count_pending_calls_on_open_leads(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_leads');

        $open = Lead::factory()->create(['status' => LeadStatus::FollowUp->value]);
        $won = Lead::factory()->create(['status' => LeadStatus::Won->value]);

        $this->followup($open, 'call', now()->toDateString());               // today
        $this->followup($open, 'whatsapp', now()->subDays(3)->toDateString()); // overdue
        $this->followup($open, 'call', now()->addDays(5)->toDateString());    // next 7 (and 30)
        $this->followup($open, 'call', now()->addDays(20)->toDateString());   // next 30 only
        $this->followup($open, 'email', now()->toDateString());               // not a call
        $this->followup($open, 'call', now()->toDateString(), 'done');        // already done
        $this->followup($won, 'call', now()->toDateString());                 // lead closed

        $view = Livewire::actingAs($user)->test(Overview::class)->assertOk();
        $calls = $view->viewData('calls');

        $this->assertSame(['today' => 1, 'overdue' => 1, 'week' => 1, 'month' => 2], $calls['counts']);
        $this->assertCount(2, $calls['due']); // overdue + today, for the call list
        $this->assertNull($view->viewData('visits'));
        $this->assertNull($view->viewData('dues'));
    }

    public function test_visits_and_meetings_ahead_are_counted(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_leads', 'view_site_visits', 'view_meetings']);

        SiteVisit::factory()->create(['visit_date' => now()->toDateString()]);
        SiteVisit::factory()->create(['visit_date' => now()->addDays(10)->toDateString()]);
        Meeting::factory()->create(['date' => now()->addDays(2)->toDateString()]);

        $view = Livewire::actingAs($user)->test(Overview::class);

        $this->assertSame(['today' => 1, 'week' => 0, 'month' => 1], $view->viewData('visits')['counts']);
        $this->assertSame(['today' => 0, 'week' => 1, 'month' => 1], $view->viewData('meetings')['counts']);
    }

    public function test_page_needs_view_leads_and_the_dashboard_crm_card_links_to_it(): void
    {
        $this->actingAs(User::factory()->create())->get(route('crm.overview'))->assertForbidden();

        $user = User::factory()->create();
        $user->givePermissionTo('view_leads');
        $this->actingAs($user)->get(route('crm.overview'))->assertOk()->assertSee('Call Overview');

        $crm = collect(Livewire::actingAs($user)->test(Dashboard::class)->viewData('modules'))->firstWhere('label', 'CRM');
        $this->assertSame(route('crm.overview'), $crm['href']);
    }
}
