<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\GoodsReceiveService;
use App\Services\InventoryService;
use App\Services\PurchaseOrderService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeSentPurchaseOrder(float $qty = 100): PurchaseOrder
    {
        $vendor = Vendor::factory()->create();
        $project = Project::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $po = app(PurchaseOrderService::class)->create([
            'vendor_id' => $vendor->id,
            'project_id' => $project->id,
            'warehouse_id' => $warehouse->id,
            'date' => now()->toDateString(),
        ], [
            ['product_id' => $product->id, 'qty' => $qty, 'rate' => 10],
        ]);

        return app(PurchaseOrderService::class)->send($po);
    }

    public function test_creating_a_grn_increases_warehouse_stock_and_records_a_purchase_movement(): void
    {
        $po = $this->makeSentPurchaseOrder();
        $item = $po->items()->first();

        $grn = app(GoodsReceiveService::class)->create($po, [], [
            ['purchase_order_item_id' => $item->id, 'received_qty' => 40, 'damaged_qty' => 0],
        ]);

        $this->assertStringStartsWith('GRN-', $grn->grn_no);

        $stock = WarehouseStock::where('warehouse_id', $po->warehouse_id)->where('product_id', $item->product_id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(40, $stock->quantity);

        $this->assertSame(1, StockMovement::where('reference_type', \App\Models\GoodsReceive::class)
            ->where('reference_id', $grn->id)
            ->where('type', StockMovementType::Purchase->value)
            ->count());
    }

    public function test_damaged_qty_is_excluded_from_the_stock_increase(): void
    {
        $po = $this->makeSentPurchaseOrder();
        $item = $po->items()->first();

        app(GoodsReceiveService::class)->create($po, [], [
            ['purchase_order_item_id' => $item->id, 'received_qty' => 40, 'damaged_qty' => 10],
        ]);

        $inventory = app(InventoryService::class);
        $this->assertEquals(30, $inventory->currentStock($item->product, $po->warehouse));
    }

    public function test_damaged_qty_exceeding_received_qty_is_rejected(): void
    {
        $po = $this->makeSentPurchaseOrder();
        $item = $po->items()->first();

        $this->expectException(\InvalidArgumentException::class);

        app(GoodsReceiveService::class)->create($po, [], [
            ['purchase_order_item_id' => $item->id, 'received_qty' => 10, 'damaged_qty' => 20],
        ]);
    }

    public function test_receipt_status_moves_to_partially_received_then_received_across_two_grns(): void
    {
        $po = $this->makeSentPurchaseOrder(100);
        $item = $po->items()->first();

        $service = app(GoodsReceiveService::class);

        $service->create($po, [], [
            ['purchase_order_item_id' => $item->id, 'received_qty' => 40, 'damaged_qty' => 0],
        ]);

        $this->assertSame(PurchaseOrderStatus::PartiallyReceived, $po->fresh()->status);

        $service->create($po, [], [
            ['purchase_order_item_id' => $item->id, 'received_qty' => 60, 'damaged_qty' => 0],
        ]);

        $this->assertSame(PurchaseOrderStatus::Received, $po->fresh()->status);
    }
}
