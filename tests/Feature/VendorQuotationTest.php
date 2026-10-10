<?php

namespace Tests\Feature;

use App\Enums\PurchaseRequestStatus;
use App\Enums\VendorQuotationStatus;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Services\VendorQuotationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorQuotationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_creating_a_vendor_quotation_computes_total_amount_from_items(): void
    {
        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::SentForQuotation->value]);
        $vendor = Vendor::factory()->create();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $vq = app(VendorQuotationService::class)->create($pr, ['vendor_id' => $vendor->id], [
            ['product_id' => $productA->id, 'qty' => 10, 'rate' => 50],
            ['product_id' => $productB->id, 'qty' => 2, 'rate' => 100],
        ]);

        $this->assertEquals(700, $vq->total_amount);
        $this->assertSame(2, $vq->items()->count());
    }

    public function test_creating_a_vendor_quotation_before_the_request_is_sent_for_quotation_is_refused(): void
    {
        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::Draft->value]);
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create();

        $this->expectException(\RuntimeException::class);

        app(VendorQuotationService::class)->create($pr, ['vendor_id' => $vendor->id], [
            ['product_id' => $product->id, 'qty' => 1, 'rate' => 10],
        ]);
    }

    public function test_selecting_a_winner_rejects_siblings_and_flips_the_request_to_vendor_selected(): void
    {
        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::SentForQuotation->value]);
        $winner = VendorQuotation::factory()->for($pr, 'purchaseRequest')->create(['status' => VendorQuotationStatus::Received->value]);
        $loser = VendorQuotation::factory()->for($pr, 'purchaseRequest')->create(['status' => VendorQuotationStatus::Received->value]);

        app(VendorQuotationService::class)->selectWinner($winner);

        $this->assertSame(VendorQuotationStatus::Selected, $winner->fresh()->status);
        $this->assertSame(VendorQuotationStatus::Rejected, $loser->fresh()->status);
        $this->assertSame(PurchaseRequestStatus::VendorSelected, $pr->fresh()->status);
    }
}
