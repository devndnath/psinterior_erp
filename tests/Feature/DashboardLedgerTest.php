<?php

namespace Tests\Feature;

use App\Enums\ExpenseClaimStatus;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\VendorInvoiceStatus;
use App\Livewire\Dashboard\Index as Dashboard;
use App\Models\Employee;
use App\Models\ExpenseClaim;
use App\Models\Invoice;
use App\Models\SubcontractorAssignment;
use App\Models\User;
use App\Models\VendorInvoice;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Receivables / Payables block at the top of the dashboard (the
 * layout of the firm's previous system).
 */
class DashboardLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_receivables_split_client_and_design_sell_and_payables_split_by_source(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_invoices', 'view_vendor_invoices', 'view_subcontractors', 'view_expense_claims']);

        // Receivables: outstanding only, drafts and cancelled ignored.
        Invoice::factory()->create(['status' => InvoiceStatus::PartiallyPaid->value, 'grand_total' => 10000, 'paid_amount' => 4000]);
        Invoice::factory()->create(['status' => InvoiceStatus::Draft->value, 'grand_total' => 99999]);
        Invoice::factory()->create(['project_id' => null, 'type' => InvoiceType::Design->value, 'status' => InvoiceStatus::Issued->value, 'grand_total' => 2500, 'paid_amount' => 0]);

        // Vendor: only bills still awaiting approval.
        VendorInvoice::factory()->create(['amount' => 3000]);
        VendorInvoice::factory()->create(['amount' => 7000, 'status' => VendorInvoiceStatus::Approved->value]);

        // Contractor: 50% of 20,000 done, 4,000 paid -> 6,000 owed.
        $job = SubcontractorAssignment::factory()->create(['rate' => 20000, 'progress' => 50]);
        $job->payments()->create(['date' => now()->toDateString(), 'amount' => 4000]);
        // Overpaid job never goes negative.
        $paidUp = SubcontractorAssignment::factory()->create(['rate' => 10000, 'progress' => 10]);
        $paidUp->payments()->create(['date' => now()->toDateString(), 'amount' => 5000]);

        // Conveyance: approved and not yet in a payroll.
        $employee = Employee::factory()->create();
        foreach ([[ExpenseClaimStatus::Approved, 450], [ExpenseClaimStatus::Pending, 900]] as [$status, $amount]) {
            ExpenseClaim::create(['employee_id' => $employee->id, 'type' => 'conveyance', 'date' => now()->toDateString(), 'amount' => $amount, 'status' => $status->value]);
        }

        $view = Livewire::actingAs($user)->test(Dashboard::class);

        $ar = collect($view->viewData('ledgerReceivables')['rows'])->keyBy('key');
        $this->assertEquals(6000, $ar['client']['amount']);
        $this->assertEquals(2500, $ar['design']['amount']);
        $this->assertEquals(8500, $view->viewData('ledgerReceivables')['total']);

        $ap = collect($view->viewData('ledgerPayables')['rows'])->keyBy('key');
        $this->assertEquals(3000, $ap['vendor']['amount']);
        $this->assertEquals(6000, $ap['contractor']['amount']);
        $this->assertEquals(450, $ap['conveyance']['amount']);
        $this->assertEquals(9450, $view->viewData('ledgerPayables')['total']);

        $view->assertSee('Receivables')->assertSee('Design Sell')->assertSee('Payables')->assertSee('Contractor');
    }

    public function test_sides_and_rows_are_hidden_without_the_permissions(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_subcontractors', 'view_leads']);

        $view = Livewire::actingAs($user)->test(Dashboard::class);

        $this->assertNull($view->viewData('ledgerReceivables'));
        $this->assertSame(['contractor'], array_column($view->viewData('ledgerPayables')['rows'], 'key'));
        $this->assertSame(['CRM'], array_column($view->viewData('modules'), 'label'));
    }
}
