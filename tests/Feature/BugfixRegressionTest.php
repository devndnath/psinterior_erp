<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\StockTransferStatus;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Project;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\StockTransferService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for bugs found in the November 2026 project-wide
 * review, each of which had no test before.
 */
class BugfixRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_clearing_an_optional_field_on_edit_actually_clears_it(): void
    {
        $invoice = Invoice::factory()->create(['notes' => 'Old note', 'due_date' => now()->addDays(10)->toDateString()]);

        $updated = app(InvoiceService::class)->update($invoice, [
            'notes' => null,
            'due_date' => null,
        ], [['description' => 'Work', 'qty' => 1, 'rate' => 1000]]);

        $this->assertNull($updated->notes);
        $this->assertNull($updated->due_date);
    }

    public function test_editing_a_part_paid_invoice_down_to_the_paid_amount_marks_it_paid(): void
    {
        $service = app(InvoiceService::class);
        $invoice = Invoice::factory()->create();
        $invoice = $service->update($invoice, [], [['description' => 'Work', 'qty' => 1, 'rate' => 1000]]);
        $invoice = $service->issue($invoice);

        app(PaymentService::class)->create([
            'client_id' => $invoice->client_id,
            'project_id' => $invoice->project_id,
            'invoice_id' => $invoice->id,
            'date' => now()->toDateString(),
            'amount' => 600,
            'method' => 'cash',
        ]);
        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->fresh()->status);

        $edited = $service->update($invoice->fresh(), [], [['description' => 'Work', 'qty' => 1, 'rate' => 600]]);

        $this->assertSame(InvoiceStatus::Paid, $edited->fresh()->status);
    }

    public function test_a_payment_cannot_settle_another_projects_invoice(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Issued->value]);
        $otherProject = Project::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        app(PaymentService::class)->create([
            'client_id' => $otherProject->client_id,
            'project_id' => $otherProject->id,
            'invoice_id' => $invoice->id,
            'date' => now()->toDateString(),
            'amount' => 100,
            'method' => 'cash',
        ]);
    }

    public function test_a_payment_cannot_be_applied_to_a_cancelled_invoice(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Cancelled->value]);

        $this->expectException(\InvalidArgumentException::class);

        app(PaymentService::class)->create([
            'client_id' => $invoice->client_id,
            'project_id' => $invoice->project_id,
            'invoice_id' => $invoice->id,
            'date' => now()->toDateString(),
            'amount' => 100,
            'method' => 'cash',
        ]);
    }

    public function test_a_stock_transfer_cannot_be_completed_twice(): void
    {
        $from = Warehouse::factory()->create();
        $to = Warehouse::factory()->create();
        $product = Product::factory()->create();
        app(InventoryService::class)->openingStock($product, $from, 10);

        $transfer = StockTransfer::factory()->create(['from_warehouse_id' => $from->id, 'to_warehouse_id' => $to->id]);
        $transfer->items()->create(['product_id' => $product->id, 'quantity' => 4]);

        $service = app(StockTransferService::class);
        $service->complete($transfer);

        try {
            $service->complete($transfer);
            $this->fail('A second completion should have been refused.');
        } catch (\RuntimeException) {
            // expected
        }

        $inventory = app(InventoryService::class);
        $this->assertEquals(6, $inventory->currentStock($product, $from));
        $this->assertEquals(4, $inventory->currentStock($product, $to));
        $this->assertSame(StockTransferStatus::Completed, $transfer->fresh()->status);
    }

    public function test_the_contract_pdf_renders_with_line_items(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('view_contracts');

        $contract = Contract::factory()->create();
        $contract->items()->create(['description' => 'Design fee', 'amount' => 50000]);
        $contract->items()->create(['description' => 'Execution', 'amount' => 450000]);

        $this->actingAs($user)->get(route('contracts.pdf', $contract))->assertOk();
    }
}
