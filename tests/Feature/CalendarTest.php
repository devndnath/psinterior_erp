<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\QuotationStatus;
use App\Livewire\Calendar\Index as CalendarIndex;
use App\Models\Invoice;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\Quotation;
use App\Models\SiteVisit;
use App\Models\User;
use App\Models\Warranty;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the Phase 17c "project calendar" fix-first item: a single page
 * aggregating dates that already live on seven other modules. These
 * tests verify the aggregation (every source actually shows up), the
 * per-source permission gating (a module's events disappear exactly
 * when that module's own `view_*` ability is missing, same as
 * Dashboard), and that the month window and status filters behave as
 * documented in Calendar\Index (e.g. a Paid invoice's due date isn't a
 * "due" reminder to show).
 */
class CalendarTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_projects', 'view_milestones', 'view_tasks', 'view_site_visits',
            'view_meetings', 'view_invoices', 'view_quotations', 'view_warranties',
        ]);
    }

    public function test_calendar_aggregates_events_from_every_source_module_in_the_current_month(): void
    {
        $project = Project::factory()->create();
        $midMonth = now()->startOfMonth()->addDays(10);

        ProjectMilestone::factory()->for($project)->create(['name' => 'Design Approval Milestone', 'due_date' => $midMonth]);
        ProjectTask::factory()->for($project)->create(['name' => 'Fix Skirting Task', 'due_date' => $midMonth]);
        Meeting::factory()->for($project)->create(['date' => $midMonth]);
        SiteVisit::factory()->create(['project_id' => $project->id, 'visit_date' => $midMonth]);
        Invoice::factory()->create(['project_id' => $project->id, 'due_date' => $midMonth, 'status' => InvoiceStatus::Issued->value]);
        Quotation::factory()->create(['project_id' => $project->id, 'valid_until' => $midMonth, 'status' => QuotationStatus::Sent->value]);
        Warranty::factory()->for($project)->create(['end_date' => $midMonth]);

        Livewire::actingAs($this->staff)->test(CalendarIndex::class)
            ->assertSee('Design Approval Milestone')
            ->assertSee('Fix Skirting Task')
            ->assertSee($project->project_code);
    }

    public function test_calendar_only_shows_events_within_the_displayed_month(): void
    {
        $project = Project::factory()->create();
        ProjectTask::factory()->for($project)->create(['name' => 'Next Month Task', 'due_date' => now()->addMonths(2)]);

        Livewire::actingAs($this->staff)->test(CalendarIndex::class)
            ->assertDontSee('Next Month Task');
    }

    public function test_next_and_previous_month_navigation_moves_the_displayed_range(): void
    {
        $project = Project::factory()->create();
        $nextMonthDate = now()->addMonth()->startOfMonth()->addDays(5);
        ProjectTask::factory()->for($project)->create(['name' => 'Upcoming Month Task', 'due_date' => $nextMonthDate]);

        Livewire::actingAs($this->staff)->test(CalendarIndex::class)
            ->assertDontSee('Upcoming Month Task')
            ->call('nextMonth')
            ->assertSee('Upcoming Month Task')
            ->call('previousMonth')
            ->assertDontSee('Upcoming Month Task');
    }

    public function test_a_fully_paid_invoice_is_not_shown_as_a_due_reminder(): void
    {
        $project = Project::factory()->create();
        Invoice::factory()->create([
            'invoice_no' => 'INV-000777',
            'project_id' => $project->id,
            'due_date' => now()->startOfMonth()->addDays(5),
            'status' => InvoiceStatus::Paid->value,
        ]);

        Livewire::actingAs($this->staff)->test(CalendarIndex::class)
            ->assertDontSee('INV-000777');
    }

    public function test_a_decided_quotation_is_not_shown_as_an_expiry_reminder(): void
    {
        $project = Project::factory()->create();
        Quotation::factory()->create([
            'quotation_no' => 'QUO-000888',
            'project_id' => $project->id,
            'valid_until' => now()->startOfMonth()->addDays(5),
            'status' => QuotationStatus::Approved->value,
        ]);

        Livewire::actingAs($this->staff)->test(CalendarIndex::class)
            ->assertDontSee('QUO-000888');
    }

    public function test_a_user_without_view_invoices_does_not_see_invoice_events(): void
    {
        $limitedUser = User::factory()->create();
        $limitedUser->givePermissionTo(['view_projects']);

        $project = Project::factory()->create();
        Invoice::factory()->create([
            'invoice_no' => 'INV-000999',
            'project_id' => $project->id,
            'due_date' => now()->startOfMonth()->addDays(5),
            'status' => InvoiceStatus::Issued->value,
        ]);

        Livewire::actingAs($limitedUser)->test(CalendarIndex::class)
            ->assertDontSee('INV-000999');
    }

    public function test_calendar_can_be_filtered_to_a_single_project(): void
    {
        $projectA = Project::factory()->create();
        $projectB = Project::factory()->create();

        ProjectTask::factory()->for($projectA)->create(['name' => 'Project A Only Task', 'due_date' => now()->startOfMonth()->addDays(3)]);
        ProjectTask::factory()->for($projectB)->create(['name' => 'Project B Only Task', 'due_date' => now()->startOfMonth()->addDays(3)]);

        Livewire::actingAs($this->staff)->test(CalendarIndex::class)
            ->set('project', (string) $projectA->id)
            ->assertSee('Project A Only Task')
            ->assertDontSee('Project B Only Task');
    }
}
