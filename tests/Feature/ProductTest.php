<?php

namespace Tests\Feature;

use App\Livewire\Products\Form as ProductForm;
use App\Livewire\Products\Index as ProductIndex;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_inventory', 'create_inventory', 'edit_inventory', 'delete_inventory',
        ]);
    }

    public function test_a_user_without_view_permission_cannot_see_the_product_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ProductIndex::class)
            ->assertForbidden();
    }

    public function test_creating_a_product_works(): void
    {
        $category = ProductCategory::factory()->create();
        $unit = Unit::factory()->create();

        Livewire::actingAs($this->staff)->test(ProductForm::class)
            ->set('sku', 'SKU-001')
            ->set('name', 'Plywood Sheet')
            ->set('category_id', (string) $category->id)
            ->set('unit_id', (string) $unit->id)
            ->set('purchase_price', '1500')
            ->set('min_stock', '10')
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::first();
        $this->assertNotNull($product);
        $this->assertSame('SKU-001', $product->sku);
        $this->assertEquals(10, $product->min_stock);
    }

    public function test_editing_a_product_requires_edit_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('view_inventory');

        $product = Product::factory()->create();

        Livewire::actingAs($viewer)->test(ProductForm::class, ['product' => $product])
            ->assertForbidden();
    }

    public function test_total_stock_sums_quantity_across_warehouses(): void
    {
        $product = Product::factory()->create(['min_stock' => 20]);
        $warehouseA = Warehouse::factory()->create();
        $warehouseB = Warehouse::factory()->create();

        $inventory = app(InventoryService::class);
        $inventory->openingStock($product, $warehouseA, 15);
        $inventory->openingStock($product, $warehouseB, 10);

        $this->assertEquals(25, $product->totalStock());
    }

    public function test_is_low_stock_compares_total_stock_to_min_stock(): void
    {
        $product = Product::factory()->create(['min_stock' => 20]);
        $warehouse = Warehouse::factory()->create();

        $inventory = app(InventoryService::class);
        $inventory->openingStock($product, $warehouse, 5);

        $this->assertTrue($product->isLowStock());

        $inventory->openingStock($product, $warehouse, 20);

        $this->assertFalse($product->isLowStock());
    }
}
