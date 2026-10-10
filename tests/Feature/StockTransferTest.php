<?php

namespace Tests\Feature;

use App\Livewire\StockTransfers\Index as StockTransferIndex;
use App\Livewire\StockTransfers\Show as StockTransferShow;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\InventoryService;
use App\Services\StockTransferService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_warehouses', 'create_warehouses', 'manage_inventory',
        ]);
    }

    public function test_a_user_without_view_permission_cannot_see_the_transfer_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(StockTransferIndex::class)
            ->assertForbidden();
    }

    public function test_completing_a_transfer_requires_manage_inventory_distinct_from_create(): void
    {
        $creatorOnly = User::factory()->create();
        $creatorOnly->givePermissionTo(['view_warehouses', 'create_warehouses']);

        $transfer = StockTransfer::factory()->create();

        Livewire::actingAs($creatorOnly)->test(StockTransferShow::class, ['stockTransfer' => $transfer])
            ->call('complete')
            ->assertForbidden();
    }

    public function test_completing_a_transfer_moves_stock_from_source_to_destination(): void
    {
        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $inventory = app(InventoryService::class);
        $inventory->openingStock($product, $fromWarehouse, 100);

        $service = app(StockTransferService::class);
        $transfer = $service->create([
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
        ], [
            ['product_id' => $product->id, 'quantity' => 40],
        ]);

        $service->complete($transfer, $this->staff->id);

        $this->assertEquals(60, $inventory->currentStock($product, $fromWarehouse));
        $this->assertEquals(40, $inventory->currentStock($product, $toWarehouse));

        $this->assertEquals(2, StockMovement::where('reference_type', StockTransfer::class)
            ->where('reference_id', $transfer->id)
            ->count());
    }

    public function test_completing_a_transfer_rolls_back_when_source_stock_is_insufficient(): void
    {
        $fromWarehouse = Warehouse::factory()->create();
        $toWarehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $inventory = app(InventoryService::class);
        $inventory->openingStock($product, $fromWarehouse, 10);

        $service = app(StockTransferService::class);
        $transfer = $service->create([
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
        ], [
            ['product_id' => $product->id, 'quantity' => 40],
        ]);

        try {
            $service->complete($transfer);
            $this->fail('Expected a RuntimeException for insufficient stock.');
        } catch (\RuntimeException $e) {
            // expected
        }

        $this->assertEquals(10, $inventory->currentStock($product, $fromWarehouse));
        $this->assertFalse(WarehouseStock::where('warehouse_id', $toWarehouse->id)->where('product_id', $product->id)->exists());
        $this->assertSame('pending', $transfer->fresh()->status->value);
    }
}
