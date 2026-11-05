<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Livewire\Expenses\Form as ExpenseForm;
use App\Livewire\Reports\Viewer;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Subcontractor;
use App\Models\SubcontractorAssignment;
use App\Models\User;
use App\Reports\Registry;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The report engine: every registered report opens, the main reports'
 * figures are right, permissions hold, and CSV export works. Also the
 * office (no-project) expenses the office reports read.
 */
class ReportEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $boss;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->boss = User::factory()->create();
        $this->boss->assignRole('admin');
    }

    private function report(string $key, array $query = [])
    {
        return Livewire::withQueryParams($query)->actingAs($this->boss)->test(Viewer::class, ['report' => $key]);
    }

    private function rows(string $key, array $query = []): \Illuminate\Support\Collection
    {
        return $this->report($key, $query)->viewData('rows');
    }

    public function test_every_registered_report_opens_on_an_empty_database(): void
    {
        foreach (Registry::classes() as $class) {
            $this->actingAs($this->boss)->get(route('reports.view', $class::key()))->assertOk();
        }
        $this->actingAs($this->boss)->get(route('reports.index'))->assertOk()->assertSee('Income Statement')->assertSee('Method Wise Report');
        $this->actingAs($this->boss)->get(route('reports.view', 'no-such-report'))->assertNotFound();
    }

    public function test_reports_need_view_reports_and_their_own_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('reports.view', 'office'))->assertForbidden();

        $user->givePermissionTo('view_reports');
        $this->actingAs($user)->get(route('reports.view', 'office'))->assertOk();
        // Income Statement also needs view_finance.
        $this->actingAs($user)->get(route('reports.view', 'income-statement'))->assertForbidden();
    }

    public function test_office_expenses_have_no_project_and_feed_the_office_reports(): void
    {
        $rent = ExpenseCategory::factory()->create(['name' => 'Rent']);
        $util = ExpenseCategory::factory()->create(['name' => 'Utilities']);

        Livewire::actingAs($this->boss)->test(ExpenseForm::class)
            ->set('for', 'office')
            ->set('category_id', (string) $rent->id)
            ->set('amount', '80000')
            ->set('date', now()->toDateString())
            ->set('method', 'bank')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertNull(Expense::firstOrFail()->project_id);

        Expense::factory()->create(['project_id' => null, 'category_id' => $util->id, 'amount' => 20000, 'date' => now()->toDateString()]);
        Expense::factory()->create(['category_id' => $util->id, 'amount' => 5000, 'date' => now()->toDateString()]); // a project's — not office

        $this->assertCount(2, $this->rows('office'));
        $heads = $this->rows('head-wise-office-expense')->keyBy('head');
        $this->assertEquals(80000, $heads['Rent']['amount']);
        $this->assertEquals(80, $heads['Rent']['share']);

        $cats = $this->rows('category-wise')->keyBy('category');
        $this->assertEquals(20000, $cats['Utilities']['office']);
        $this->assertEquals(5000, $cats['Utilities']['project']);
    }

    public function test_income_statement_and_cash_book(): void
    {
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $cat = ExpenseCategory::factory()->create(['name' => 'Materials']);

        Payment::factory()->create(['client_id' => $client->id, 'project_id' => $project->id, 'amount' => 100000, 'date' => now()->toDateString(), 'method' => 'cash']);
        Payment::factory()->create(['client_id' => $client->id, 'project_id' => null, 'amount' => 30000, 'date' => now()->toDateString(), 'method' => 'bank']);
        Expense::factory()->create(['project_id' => $project->id, 'category_id' => $cat->id, 'amount' => 40000, 'date' => now()->toDateString(), 'method' => 'cash']);
        // Before the range: becomes the cash book's opening balance.
        Payment::factory()->create(['client_id' => $client->id, 'project_id' => $project->id, 'amount' => 5000, 'date' => now()->subYears(2)->toDateString(), 'method' => 'cash']);

        $year = ['from' => now()->startOfYear()->toDateString(), 'to' => now()->endOfYear()->toDateString()];
        $income = $this->rows('income-statement', $year)->keyBy('particular');
        $this->assertEquals(100000, $income['Project receipts']['income']);
        $this->assertEquals(30000, $income['Design sales']['income']);
        $this->assertEquals(40000, $income['Materials']['expense']);
        $summary = collect($this->report('income-statement', $year)->viewData('summary'))->keyBy('label');
        $this->assertEquals(90000, $summary['Net profit']['value']);

        $cash = collect($this->report('cash-book', $year + ['method' => 'cash'])->viewData('summary'))->keyBy('label');
        $this->assertEquals(5000, $cash['Opening balance']['value']);
        $this->assertEquals(100000, $cash['Received']['value']);
        $this->assertEquals(40000, $cash['Paid']['value']);
        $this->assertEquals(65000, $cash['Closing balance']['value']);
    }

    public function test_project_wise_contractor_wise_and_vat_figures(): void
    {
        $project = Project::factory()->create(['contract_value' => 500000]);
        Invoice::factory()->create(['project_id' => $project->id, 'client_id' => $project->client_id, 'status' => InvoiceStatus::Issued->value, 'grand_total' => 200000, 'tax' => 15000]);
        Payment::factory()->create(['project_id' => $project->id, 'client_id' => $project->client_id, 'amount' => 150000]);
        Expense::factory()->create(['project_id' => $project->id, 'amount' => 60000]);

        $row = $this->rows('project-wise', ['from' => '', 'to' => ''])->first();
        $this->assertEquals(500000, $row['budget']);
        $this->assertEquals(90000, $row['balance']); // received − expenses
        $this->assertEquals(50000, $row['due']);     // invoiced − received

        $sub = Subcontractor::factory()->create();
        $job = SubcontractorAssignment::factory()->create(['subcontractor_id' => $sub->id, 'rate' => 100000, 'progress' => 50]);
        $job->payments()->create(['date' => now()->toDateString(), 'amount' => 20000]);
        $c = $this->rows('contractor-wise')->first();
        $this->assertEquals(50000, $c['done']);
        $this->assertEquals(30000, $c['due']);

        PurchaseOrder::factory()->create(['status' => 'sent', 'date' => now()->toDateString(), 'total' => 115000, 'tax' => 5000]);
        $vat = collect($this->report('vat', ['from' => '', 'to' => ''])->viewData('totals'));
        $this->assertEquals(15000, $vat['output']);
        $this->assertEquals(5000, $vat['input']);
        $this->assertEquals(10000, $vat['net']);
    }

    public function test_csv_export_includes_headers_rows_and_totals(): void
    {
        Expense::factory()->create(['project_id' => null, 'amount' => 1234.5, 'date' => now()->toDateString()]);

        $response = $this->report('office')->call('exportCsv');
        $this->assertNotNull($response->effects['download'] ?? null);
        $csv = base64_decode($response->effects['download']['content']);
        $this->assertStringContainsString('Office Report', $csv);
        $this->assertStringContainsString('Amount', $csv);
        $this->assertStringContainsString('1234.50', $csv);
        $this->assertStringContainsString('Total', $csv);
    }

    public function test_the_sidebar_lists_the_reports_by_group(): void
    {
        $this->actingAs($this->boss)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Income Statement')
            ->assertSee('Cash Book')
            ->assertSee('Client Report')
            ->assertSee('Head Wise Office Expense')
            ->assertSee(route('reports.view', 'method-wise'), false);
    }
}
