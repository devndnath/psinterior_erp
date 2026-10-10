<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit-style tests for InventoryService — no Livewire involved. The
 * lockForUpdate() concurrency mechanism itself is structurally identical
 * to NumberingService::next() (already covered by that service's own
 * concurrency test from Phase 1) and isn't practically testable without
 * real parallel requests in this sandbox, so these assert correctness of
 * sequential calls only.
 */
class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $inventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventory = app(InventoryService::class);
    }

    public function test_record_movement_increments_warehouse_stock_and_sets_balance_after(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $movement = $this->inventory->recordMovement($product, $warehouse, StockMovementType::Opening, 50);

        $this->assertEquals(50, $movement->balance_after);
        $this->assertEquals(50, $this->inventory->currentStock($product, $warehouse));

        $stock = WarehouseStock::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
        $this->assertEquals(50, $stock->quantity);
    }

    public function test_record_movement_decrements_warehouse_stock(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->inventory->recordMovement($product, $warehouse, StockMovementType::Opening, 50);
        $movement = $this->inventory->recordMovement($product, $warehouse, StockMovementType::Issue, -20);

        $this->assertEquals(30, $movement->balance_after);
        $this->assertEquals(30, $this->inventory->currentStock($product, $warehouse));
    }

    public function test_record_movement_throws_when_resulting_quantity_would_go_negative(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->inventory->recordMovement($product, $warehouse, StockMovementType::Opening, 10);

        $this->expectException(\RuntimeException::class);

        $this->inventory->recordMovement($product, $warehouse, StockMovementType::Issue, -20);
    }

    public function test_record_movement_does_not_change_stock_when_it_throws(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->inventory->recordMovement($product, $warehouse, StockMovementType::Opening, 10);

        try {
            $this->inventory->recordMovement($product, $warehouse, StockMovementType::Issue, -20);
        } catch (\RuntimeException $e) {
            // expected
        }

        $this->assertEquals(10, $this->inventory->currentStock($product, $warehouse));
    }

    public function test_adjust_allows_a_negative_result_via_allow_negative(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->inventory->recordMovement($product, $warehouse, StockMovementType::Opening, 10);
        $movement = $this->inventory->adjust($product, $warehouse, -15);

        $this->assertEquals(-5, $movement->balance_after);
        $this->assertEquals(-5, $this->inventory->currentStock($product, $warehouse));
    }

    public function test_opening_stock_self_registers_the_warehouse_stock_row(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->assertDatabaseMissing('warehouse_stocks', ['warehouse_id' => $warehouse->id, 'product_id' => $product->id]);

        $this->inventory->openingStock($product, $warehouse, 100);

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 100,
        ]);
    }
}
