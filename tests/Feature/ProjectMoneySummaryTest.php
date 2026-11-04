<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Livewire\Designs\Index as DesignIndex;
use App\Livewire\Projects\Index as ProjectIndex;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Budget / Paid / Balance on the Projects list (balance = paid minus the
 * project's expenses), the money summary above it, and the design-sales
 * summary on the Designs list.
 */
class ProjectMoneySummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function finance(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_projects', 'view_finance', 'view_designs', 'view_invoices']);

        return $user;
    }

    public function test_rows_and_summary_show_budget_paid_and_balance(): void
    {
        $a = Project::factory()->create(['contract_value' => 100000, 'estimated_budget' => 80000]);
        $b = Project::factory()->create(['contract_value' => null, 'estimated_budget' => 50000]);

        Payment::factory()->create(['project_id' => $a->id, 'client_id' => $a->client_id, 'amount' => 60000]);
        Expense::factory()->create(['project_id' => $a->id, 'amount' => 25000]);
        Expense::factory()->create(['project_id' => $b->id, 'amount' => 7000]);

        $view = Livewire::actingAs($this->finance())->test(ProjectIndex::class);

        $rows = $view->viewData('projects')->keyBy('id');
        $this->assertEquals(100000, $rows[$a->id]->budget_value); // contract value wins
        $this->assertEquals(50000, $rows[$b->id]->budget_value);  // falls back to estimate
        $this->assertEquals(60000, $rows[$a->id]->paid_sum);

        $summary = $view->viewData('summary');
        $this->assertSame(2, $summary['count']);
        $this->assertEquals(150000, $summary['budget']);
        $this->assertEquals(60000, $summary['paid']);
        $this->assertEquals(32000, $summary['expenses']);
        $this->assertEquals(28000, $summary['balance']);

        $view->assertSee('35,000.00')   // project A balance: 60,000 - 25,000
            ->assertSee('-7,000.00');    // project B balance: 0 - 7,000
    }

    public function test_sorting_by_balance_puts_the_lowest_first(): void
    {
        $loss = Project::factory()->create(['name' => 'Loss Project']);
        $gain = Project::factory()->create(['name' => 'Gain Project']);
        Expense::factory()->create(['project_id' => $loss->id, 'amount' => 9000]);
        Payment::factory()->create(['project_id' => $gain->id, 'client_id' => $gain->client_id, 'amount' => 5000]);

        $ids = Livewire::withQueryParams(['sort' => 'balance', 'direction' => 'asc'])
            ->actingAs($this->finance())->test(ProjectIndex::class)
            ->viewData('projects')->pluck('id')->all();

        $this->assertSame([$loss->id, $gain->id], $ids);
    }

    public function test_money_is_hidden_without_view_finance_and_bad_sorts_are_ignored(): void
    {
        Project::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo('view_projects');

        $view = Livewire::withQueryParams(['sort' => 'nonsense; drop table'])
            ->actingAs($user)->test(ProjectIndex::class)->assertOk();

        $this->assertNull($view->viewData('summary'));
        $view->assertDontSee('Balance');
    }

    public function test_designs_page_summarises_design_sales(): void
    {
        Invoice::factory()->create(['project_id' => null, 'type' => InvoiceType::Design->value, 'status' => InvoiceStatus::PartiallyPaid->value, 'grand_total' => 30000, 'paid_amount' => 12000]);
        Invoice::factory()->create(['project_id' => null, 'type' => InvoiceType::Design->value, 'status' => InvoiceStatus::Cancelled->value, 'grand_total' => 99999]);
        Invoice::factory()->create(['type' => InvoiceType::Progress->value, 'grand_total' => 50000]);

        $sales = Livewire::actingAs($this->finance())->test(DesignIndex::class)->viewData('summary')['sales'];

        $this->assertEquals(30000, $sales['invoiced']);
        $this->assertEquals(12000, $sales['received']);
        $this->assertEquals(18000, $sales['due']);
        $this->assertSame(1, $sales['count']);
    }
}
