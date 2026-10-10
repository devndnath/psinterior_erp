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
}
