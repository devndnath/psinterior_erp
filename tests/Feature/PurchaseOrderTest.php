<?php

namespace Tests\Feature;

use App\Enums\PurchaseRequestStatus;
use App\Enums\VendorQuotationStatus;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\Warehouse;
use App\Services\PurchaseOrderService;
use App\Services\VendorQuotationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_create_from_vendor_quotation_requires_the_quotation_to_be_selected(): void
    {
        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::VendorSelected->value]);
        $vq = VendorQuotation::factory()->for($pr, 'purchaseRequest')->create(['status' => VendorQuotationStatus::Received->value]);
        $warehouse = Warehouse::factory()->create();

        $this->expectException(\RuntimeException::class);

        app(PurchaseOrderService::class)->createFromVendorQuotation($vq, ['warehouse_id' => $warehouse->id]);
    }

    public function test_create_from_vendor_quotation_copies_items_and_flips_the_request_to_po_created(): void
    {
        $project = Project::factory()->create();
        $pr = PurchaseRequest::factory()->for($project)->create(['status' => PurchaseRequestStatus::SentForQuotation->value]);
        $vendor = Vendor::factory()->create();
        $vq = VendorQuotation::factory()->for($pr, 'purchaseRequest')->for($vendor)->create(['status' => VendorQuotationStatus::Received->value]);

        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $vq->items()->create(['product_id' => $productA->id, 'qty' => 5, 'rate' => 20, 'amount' => 100]);
        $vq->items()->create(['product_id' => $productB->id, 'qty' => 3, 'rate' => 30, 'amount' => 90]);

        app(VendorQuotationService::class)->selectWinner($vq);

        $warehouse = Warehouse::factory()->create();

        $po = app(PurchaseOrderService::class)->createFromVendorQuotation($vq->fresh(), [
            'warehouse_id' => $warehouse->id,
            'date' => now()->toDateString(),
        ]);

        $this->assertStringStartsWith('PO-', $po->po_no);
        $this->assertSame($vendor->id, $po->vendor_id);
        $this->assertSame($pr->id, $po->purchase_request_id);
        $this->assertSame($vq->id, $po->vendor_quotation_id);
        $this->assertSame(2, $po->items()->count());
        $this->assertEquals(190, $po->subtotal);
        $this->assertEquals(190, $po->total);
        $this->assertSame(PurchaseRequestStatus::PoCreated, $pr->fresh()->status);
    }

    public function test_ad_hoc_create_computes_totals_from_items(): void
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
            'discount' => 10,
            'tax' => 5,
        ], [
            ['product_id' => $product->id, 'qty' => 4, 'rate' => 25],
        ]);

        $this->assertEquals(100, $po->subtotal);
        $this->assertEquals(95, $po->total);
    }
}
