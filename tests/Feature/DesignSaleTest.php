<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Livewire\Payments\Form as PaymentForm;
use App\Livewire\Reports\FinancialSummary;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Design work is also sold on its own, to a client with no project: the
 * invoice and payment carry only the client, and the income shows up as
 * "Design sales" in the reports and on the dashboard.
 */
class DesignSaleTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_invoices', 'create_invoices', 'edit_invoices', 'approve_invoice',
            'view_payments', 'create_payments', 'view_reports',
        ]);
    }

    public function test_a_design_sale_invoice_can_be_created_without_a_project(): void
    {
        $client = Client::factory()->create();

        Livewire::actingAs($this->staff)->test(InvoiceForm::class)
            ->set('billing', 'design')
            ->assertSet('type', InvoiceType::Design->value)
            ->set('client_id', (string) $client->id)
            ->set('items.0.description', '3D kitchen design')
            ->set('items.0.qty', '1')
            ->set('items.0.rate', '25000')
            ->call('save')
            ->assertHasNoErrors();

        $invoice = Invoice::firstOrFail();
        $this->assertNull($invoice->project_id);
        $this->assertSame($client->id, $invoice->client_id);
        $this->assertSame(InvoiceType::Design, $invoice->type);
        $this->assertEquals(25000, $invoice->grand_total);
    }

    public function test_a_design_sale_needs_a_client_and_a_project_invoice_needs_a_project(): void
    {
        Livewire::actingAs($this->staff)->test(InvoiceForm::class)
            ->set('billing', 'design')
            ->set('items.0.description', 'Design')
            ->call('save')
            ->assertHasErrors(['client_id' => 'required_if']);

        Livewire::actingAs($this->staff)->test(InvoiceForm::class)
            ->set('items.0.description', 'Work')
            ->call('save')
            ->assertHasErrors(['project_id' => 'required_if']);
    }

    public function test_the_create_link_from_a_client_preselects_a_design_sale(): void
    {
        $client = Client::factory()->create();

        Livewire::withQueryParams(['billing' => 'design', 'client' => (string) $client->id])
            ->actingAs($this->staff)->test(InvoiceForm::class)
            ->assertSet('billing', 'design')
            ->assertSet('client_id', (string) $client->id)
            ->assertSet('type', InvoiceType::Design->value);
    }

    public function test_a_payment_settles_a_design_sale_invoice(): void
    {
        $client = Client::factory()->create();
        $invoices = app(InvoiceService::class);
        $invoice = $invoices->issue($invoices->create(null, ['client_id' => $client->id], [
            ['description' => 'Living room concept', 'qty' => 1, 'rate' => 18000],
        ]));

        Livewire::withQueryParams(['invoice' => (string) $invoice->id])
            ->actingAs($this->staff)->test(PaymentForm::class)
            ->assertSet('billing', 'design')
            ->assertSet('client_id', (string) $client->id)
            ->set('amount', '18000')
            ->call('save')
            ->assertHasNoErrors();

        $payment = Payment::firstOrFail();
        $this->assertNull($payment->project_id);
        $this->assertSame($client->id, $payment->client_id);
        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);
    }

    public function test_a_design_sale_payment_cannot_settle_another_clients_invoice(): void
    {
        $invoices = app(InvoiceService::class);
        $invoice = $invoices->issue($invoices->create(null, ['client_id' => Client::factory()->create()->id], [
            ['description' => 'Design', 'qty' => 1, 'rate' => 1000],
        ]));

        $this->expectException(\InvalidArgumentException::class);

        app(PaymentService::class)->create([
            'client_id' => Client::factory()->create()->id,
            'project_id' => null,
            'invoice_id' => $invoice->id,
            'date' => now()->toDateString(),
            'amount' => 1000,
            'method' => 'cash',
        ]);
    }

    public function test_the_financial_summary_splits_design_income_from_project_income(): void
    {
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $invoices = app(InvoiceService::class);
        $payments = app(PaymentService::class);

        $design = $invoices->issue($invoices->create(null, ['client_id' => $client->id], [
            ['description' => 'Design', 'qty' => 1, 'rate' => 20000],
        ]));
        $work = $invoices->issue($invoices->create($project, [], [
            ['description' => 'Execution', 'qty' => 1, 'rate' => 100000],
        ]));

        foreach ([[$design, null, 20000], [$work, $project->id, 40000]] as [$inv, $projectId, $amount]) {
            $payments->create([
                'client_id' => $client->id, 'project_id' => $projectId, 'invoice_id' => $inv->id,
                'date' => now()->toDateString(), 'amount' => $amount, 'method' => 'bank',
            ]);
        }

        $totals = Livewire::actingAs($this->staff)->test(FinancialSummary::class)->viewData('totals');

        $this->assertEquals(20000, $totals['design_invoiced']);
        $this->assertEquals(20000, $totals['design_received']);
        $this->assertEquals(100000, $totals['project_invoiced']);
        $this->assertEquals(40000, $totals['project_received']);
    }

    public function test_ageing_puts_a_long_overdue_invoice_in_the_90_plus_bucket(): void
    {
        Invoice::factory()->create([
            'status' => InvoiceStatus::Issued->value,
            'invoice_date' => now()->subDays(130)->toDateString(),
            'due_date' => now()->subDays(120)->toDateString(),
            'grand_total' => 5000,
        ]);

        $ageing = Livewire::actingAs($this->staff)->test(FinancialSummary::class)->viewData('ageing');

        $this->assertEquals(5000, $ageing['90+']);
        $this->assertEquals(0, $ageing['0-30']);
    }
}
