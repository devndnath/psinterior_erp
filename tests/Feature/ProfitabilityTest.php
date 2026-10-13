<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Services\ProfitabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_project_computes_profit_and_margin_from_known_fixture_data(): void
    {
        $project = Project::factory()->create(['contract_value' => 1000000]);

        Invoice::factory()->for($project)->create(['grand_total' => 400000, 'status' => 'issued']);
        Invoice::factory()->for($project)->create(['grand_total' => 999999, 'status' => 'cancelled']);

        Payment::factory()->for($project)->create(['amount' => 250000]);

        Expense::factory()->for($project)->create(['amount' => 150000]);
        Expense::factory()->for($project)->create(['amount' => 50000]);

        $result = app(ProfitabilityService::class)->forProject($project);

        $this->assertEquals(1000000, $result['contract_value']);
        $this->assertEquals(400000, $result['total_invoiced']); // cancelled invoice excluded
        $this->assertEquals(250000, $result['total_received']);
        $this->assertEquals(200000, $result['total_expenses']);
        $this->assertEquals(800000, $result['profit']); // 1,000,000 - 200,000
        $this->assertEquals(80.0, $result['margin_percent']); // 800,000 / 1,000,000 * 100
    }

    public function test_margin_percent_is_null_when_contract_value_is_zero(): void
    {
        $project = Project::factory()->create(['contract_value' => 0]);
        Expense::factory()->for($project)->create(['amount' => 1000]);

        $result = app(ProfitabilityService::class)->forProject($project);

        $this->assertNull($result['margin_percent']);
        $this->assertEquals(-1000, $result['profit']);
    }

    public function test_budget_variance_is_computed_independently_from_contract_profit(): void
    {
        // Deliberately generous contract (so profit/margin look healthy)
        // but a tight internal budget that spending has blown past —
        // proves budget_variance is its own figure, not derived from
        // contract_value/profit.
        $project = Project::factory()->create([
            'contract_value' => 1000000,
            'estimated_budget' => 150000,
        ]);

        Expense::factory()->for($project)->create(['amount' => 200000]);

        $result = app(ProfitabilityService::class)->forProject($project);

        $this->assertEquals(800000, $result['profit']); // still healthy against contract_value
        $this->assertEquals(150000, $result['estimated_budget']);
        $this->assertEquals(-50000, $result['budget_variance']); // 150,000 - 200,000
        $this->assertEquals(-33.33, $result['budget_variance_percent']);
        $this->assertTrue($result['is_over_budget']);
    }

    public function test_budget_variance_is_null_when_no_budget_was_set(): void
    {
        $project = Project::factory()->create(['estimated_budget' => null]);
        Expense::factory()->for($project)->create(['amount' => 1000]);

        $result = app(ProfitabilityService::class)->forProject($project);

        $this->assertNull($result['estimated_budget']);
        $this->assertNull($result['budget_variance']);
        $this->assertNull($result['budget_variance_percent']);
        $this->assertNull($result['is_over_budget']);
    }

    public function test_for_all_projects_includes_budget_variance_per_row(): void
    {
        $project = Project::factory()->create([
            'contract_value' => 500000,
            'estimated_budget' => 100000,
        ]);
        Expense::factory()->for($project)->create(['amount' => 40000]);

        $row = app(ProfitabilityService::class)->forAllProjects()->firstWhere('project.id', $project->id);

        $this->assertEquals(100000, $row['estimated_budget']);
        $this->assertEquals(60000, $row['budget_variance']);
        $this->assertFalse($row['is_over_budget']);
    }
}
