<?php

namespace Tests\Feature;

use App\Livewire\Reports\FinancialSummary;
use App\Livewire\Reports\Index as ReportIndex;
use App\Livewire\Reports\ProjectStatus;
use App\Livewire\Reports\SalesFunnel;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_a_user_without_view_reports_permission_cannot_open_the_reports_hub(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ReportIndex::class)->assertForbidden();
    }

    public function test_project_status_report_lists_every_project_with_correct_figures(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_reports');

        $client = Client::factory()->create(['name' => 'Acme Interiors']);
        Project::factory()->create(['client_id' => $client->id, 'name' => 'Acme HQ Fit-out', 'contract_value' => 500000]);

        Livewire::actingAs($user)->test(ProjectStatus::class)
            ->assertSee('Acme HQ Fit-out')
            ->assertSee('Acme Interiors')
            ->assertSee(number_format(500000, 2));
    }

    public function test_sales_funnel_report_computes_conversion_rate_per_source(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_reports');

        $source = LeadSource::factory()->create(['name' => 'Referral']);
        Lead::factory()->won()->create(['lead_source_id' => $source->id]);
        Lead::factory()->lost()->create(['lead_source_id' => $source->id]);

        Livewire::actingAs($user)->test(SalesFunnel::class)
            ->assertSee('Referral')
            ->assertSee('50%');
    }

    public function test_financial_summary_report_totals_invoiced_received_and_outstanding(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_reports');

        $invoice = Invoice::factory()->create([
            'status' => 'issued',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'paid_amount' => 40000,
        ]);
        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'project_id' => $invoice->project_id,
            'amount' => 40000,
            'date' => now()->toDateString(),
        ]);
        Expense::factory()->create(['amount' => 15000, 'date' => now()->toDateString()]);

        Livewire::actingAs($user)->test(FinancialSummary::class)
            ->assertSee(number_format(100000, 2))
            ->assertSee(number_format(40000, 2))
            ->assertSee(number_format(60000, 2))
            ->assertSee(number_format(15000, 2));
    }
}
