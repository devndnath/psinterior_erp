<?php

namespace Tests\Feature;

use App\Enums\ChangeOrderStatus;
use App\Enums\LeadStatus;
use App\Models\ChangeOrder;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ChangeOrderApprovedNotification;
use App\Notifications\InvoiceOverdueNotification;
use App\Notifications\LeadConvertedNotification;
use App\Services\ChangeOrderService;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers 3 of the 6 notification triggers (different trigger styles,
 * per the brief): LeadConverted (fired directly from a service method),
 * ChangeOrderApproved (fired from a side-effect branch inside a service
 * transaction), and InvoiceOverdue (fired from the new
 * `invoices:flag-overdue` console command).
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_converting_a_lead_notifies_its_assignee(): void
    {
        $assignee = User::factory()->create();
        $lead = Lead::factory()->won()->create(['assigned_to' => $assignee->id]);

        app(LeadService::class)->convertToClient($lead);

        $this->assertSame(1, $assignee->notifications()->count());
        $this->assertSame(LeadConvertedNotification::class, $assignee->notifications()->first()->type);
    }

    public function test_approving_a_change_order_notifies_the_project_manager(): void
    {
        $pm = User::factory()->create();
        $project = Project::factory()->create(['project_manager_id' => $pm->id]);
        $changeOrder = ChangeOrder::factory()->create(['project_id' => $project->id, 'status' => ChangeOrderStatus::Pending->value]);

        app(ChangeOrderService::class)->recordClientDecision($changeOrder, 'approved');

        $this->assertSame(1, $pm->notifications()->count());
        $this->assertSame(ChangeOrderApprovedNotification::class, $pm->notifications()->first()->type);
    }

    public function test_the_flag_overdue_invoices_command_flips_status_and_notifies_the_project_manager(): void
    {
        $pm = User::factory()->create();
        $project = Project::factory()->create(['project_manager_id' => $pm->id]);
        $invoice = Invoice::factory()->create([
            'project_id' => $project->id,
            'status' => 'issued',
            'due_date' => now()->subDays(5)->toDateString(),
        ]);

        $this->artisan('invoices:flag-overdue')->assertSuccessful();

        $this->assertSame('overdue', $invoice->fresh()->status->value);
        $this->assertSame(1, $pm->notifications()->count());
        $this->assertSame(InvoiceOverdueNotification::class, $pm->notifications()->first()->type);
    }
}
