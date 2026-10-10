<?php

namespace Tests\Feature;

use App\Enums\MaterialRequisitionStatus;
use App\Livewire\MaterialRequisitions\Index as MaterialRequisitionIndex;
use App\Livewire\MaterialRequisitions\Show as MaterialRequisitionShow;
use App\Models\MaterialRequisition;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\MaterialRequisitionService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MaterialRequisitionTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo([
            'view_material_requisitions', 'create_material_requisitions',
            'edit_material_requisitions', 'delete_material_requisitions',
            'manage_inventory',
        ]);
    }

    public function test_a_user_without_view_permission_cannot_see_the_requisition_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(MaterialRequisitionIndex::class)
            ->assertForbidden();
    }

    public function test_full_approval_chain_moves_status_from_pending_to_issued(): void
    {
        $project = Project::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $inventory = app(InventoryService::class);
        $inventory->openingStock($product, $warehouse, 100);

        $service = app(MaterialRequisitionService::class);
        $mr = $service->create($project, ['date' => now()->toDateString()], [
            ['product_id' => $product->id, 'quantity' => 30],
        ]);

        $this->assertSame(MaterialRequisitionStatus::Pending, $mr->status);

        $mr = $service->approveByManager($mr);
        $this->assertSame(MaterialRequisitionStatus::ManagerApproved, $mr->status);

        $mr = $service->approveByStore($mr);
        $this->assertSame(MaterialRequisitionStatus::StoreApproved, $mr->status);

        $mr = $service->issue($mr, $warehouse->id, $this->staff->id);
        $this->assertSame(MaterialRequisitionStatus::Issued, $mr->status);

        $this->assertEquals(70, $inventory->currentStock($product, $warehouse));
        $this->assertEquals(30, $mr->items->first()->issued_quantity);
    }

    public function test_issuing_requires_a_warehouse_to_be_chosen(): void
    {
        $project = Project::factory()->create();
        $product = Product::factory()->create();
        Warehouse::factory()->create(); // an unrelated warehouse exists, but none is chosen

        $service = app(MaterialRequisitionService::class);
        $mr = $service->create($project, ['date' => now()->toDateString()], [
            ['product_id' => $product->id, 'quantity' => 10],
        ]);
        $mr = $service->approveByManager($mr);
        $mr = $service->approveByStore($mr);

        $this->expectException(\RuntimeException::class);
        $service->issue($mr);
    }

    public function test_deleting_is_refused_once_status_leaves_pending(): void
    {
        $project = Project::factory()->create();
        $mr = MaterialRequisition::factory()->for($project)->create([
            'status' => MaterialRequisitionStatus::ManagerApproved->value,
        ]);

        Livewire::actingAs($this->staff)->test(MaterialRequisitionShow::class, ['materialRequisition' => $mr])
            ->call('deleteRequisition')
            ->assertForbidden();

        $this->assertNotNull($mr->fresh());
    }

    public function test_deleting_a_pending_requisition_is_allowed(): void
    {
        $project = Project::factory()->create();
        $mr = MaterialRequisition::factory()->for($project)->create([
            'status' => MaterialRequisitionStatus::Pending->value,
        ]);

        Livewire::actingAs($this->staff)->test(MaterialRequisitionShow::class, ['materialRequisition' => $mr])
            ->call('deleteRequisition')
            ->assertHasNoErrors();

        $this->assertNull($mr->fresh());
    }
}
