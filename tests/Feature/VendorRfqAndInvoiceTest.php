<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\VendorInvoiceStatus;
use App\Enums\VendorQuotationStatus;
use App\Livewire\Vendor\Invoices\Form as VendorInvoiceForm;
use App\Livewire\Vendor\Rfqs\Show as VendorRfqShow;
use App\Livewire\VendorInvoices\Index as StaffVendorInvoiceIndex;
use App\Livewire\VendorInvoices\Show as StaffVendorInvoiceShow;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorInvoice;
use App\Services\VendorInvoiceService;
use App\Services\VendorQuotationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the Phase 17h fix-first item: vendors responding to RFQs and
 * submitting their own invoices through the vendor portal, instead of
 * staff keying everything in on their behalf. Both sides reuse
 * existing services (VendorQuotationService) or a new one shaped like
 * them (VendorInvoiceService) — the vendor-portal components are
 * ownership-checked, never gated through a staff Policy (see "Portal
 * authorization model" in PHASE11_SUMMARY.md).
 */
class VendorRfqAndInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->staff = User::factory()->create();
        $this->staff->givePermissionTo(['view_vendor_invoices', 'approve_vendor_invoice']);
    }

    protected function makeVendorUser(Vendor $vendor): User
    {
        $user = User::factory()->vendor()->create();
        $vendor->update(['user_id' => $user->id]);

        return $user;
    }

    // --- RFQ response ----------------------------------------------------

    public function test_a_vendor_can_submit_a_quotation_against_an_open_request(): void
    {
        $vendor = Vendor::factory()->create();
        $vendorUser = $this->makeVendorUser($vendor);

        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::SentForQuotation->value]);
        $product = Product::factory()->create();
        $pr->items()->create(['product_id' => $product->id, 'quantity' => 5]);

        Livewire::actingAs($vendorUser, 'vendor')->test(VendorRfqShow::class, ['purchaseRequest' => $pr])
            ->set('items.0.rate', '120')
            ->call('submitQuote')
            ->assertHasNoErrors();

        $vq = $pr->vendorQuotations()->where('vendor_id', $vendor->id)->first();
        $this->assertNotNull($vq);
        $this->assertEquals(600, $vq->total_amount);
        $this->assertSame(VendorQuotationStatus::Received, $vq->status);
    }

    public function test_a_vendor_can_revise_their_quotation_while_still_open(): void
    {
        $vendor = Vendor::factory()->create();
        $vendorUser = $this->makeVendorUser($vendor);

        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::SentForQuotation->value]);
        $product = Product::factory()->create();
        $pr->items()->create(['product_id' => $product->id, 'quantity' => 5]);

        app(VendorQuotationService::class)->create($pr, ['vendor_id' => $vendor->id], [
            ['product_id' => $product->id, 'qty' => 5, 'rate' => 100],
        ]);

        Livewire::actingAs($vendorUser, 'vendor')->test(VendorRfqShow::class, ['purchaseRequest' => $pr])
            ->set('items.0.rate', '80')
            ->call('submitQuote')
            ->assertHasNoErrors();

        $vq = $pr->vendorQuotations()->where('vendor_id', $vendor->id)->first();
        $this->assertEquals(400, $vq->total_amount);
    }

    public function test_a_vendor_cannot_revise_a_quotation_that_has_already_been_decided(): void
    {
        $vendor = Vendor::factory()->create();
        $vendorUser = $this->makeVendorUser($vendor);

        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::VendorSelected->value]);
        $product = Product::factory()->create();
        $pr->items()->create(['product_id' => $product->id, 'quantity' => 5]);

        $vq = $pr->vendorQuotations()->create([
            'vendor_id' => $vendor->id,
            'status' => VendorQuotationStatus::Selected->value,
            'total_amount' => 500,
        ]);
        $vq->items()->create(['product_id' => $product->id, 'qty' => 5, 'rate' => 100, 'amount' => 500]);

        Livewire::actingAs($vendorUser, 'vendor')->test(VendorRfqShow::class, ['purchaseRequest' => $pr])
            ->call('submitQuote')
            ->assertForbidden();

        $this->assertEquals(500, $vq->fresh()->total_amount);
    }

    public function test_a_vendor_cannot_respond_on_behalf_of_another_vendor(): void
    {
        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();
        $userA = $this->makeVendorUser($vendorA);

        $pr = PurchaseRequest::factory()->create(['status' => PurchaseRequestStatus::SentForQuotation->value]);
        $product = Product::factory()->create();
        $pr->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        Livewire::actingAs($userA, 'vendor')->test(VendorRfqShow::class, ['purchaseRequest' => $pr])
            ->set('items.0.rate', '50')
            ->call('submitQuote')
            ->assertHasNoErrors();

        $this->assertNotNull($pr->vendorQuotations()->where('vendor_id', $vendorA->id)->first());
        $this->assertNull($pr->vendorQuotations()->where('vendor_id', $vendorB->id)->first());
    }

    // --- Invoice submission -----------------------------------------------

    public function test_a_vendor_can_submit_an_invoice_against_their_own_purchase_order(): void
    {
        $vendor = Vendor::factory()->create();
        $vendorUser = $this->makeVendorUser($vendor);
        $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id, 'status' => PurchaseOrderStatus::Sent->value]);

        Livewire::actingAs($vendorUser, 'vendor')->test(VendorInvoiceForm::class)
            ->set('purchase_order_id', (string) $po->id)
            ->set('vendor_invoice_no', 'INV-1001')
            ->set('invoice_date', now()->toDateString())
            ->set('amount', '15000')
            ->call('save')
            ->assertHasNoErrors();

        $invoice = VendorInvoice::first();
        $this->assertNotNull($invoice);
        $this->assertSame($vendor->id, $invoice->vendor_id);
        $this->assertSame($po->id, $invoice->purchase_order_id);
        $this->assertSame(VendorInvoiceStatus::Submitted, $invoice->status);
    }

    public function test_a_vendor_cannot_submit_an_invoice_against_another_vendors_purchase_order(): void
    {
        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();
        $userA = $this->makeVendorUser($vendorA);
        $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id, 'status' => PurchaseOrderStatus::Sent->value]);

        Livewire::actingAs($userA, 'vendor')->test(VendorInvoiceForm::class)
            ->set('purchase_order_id', (string) $poB->id)
            ->set('invoice_date', now()->toDateString())
            ->set('amount', '1000')
            ->call('save')
            ->assertHasErrors('purchase_order_id');

        $this->assertSame(0, VendorInvoice::count());
    }

    public function test_an_invoice_cannot_be_submitted_against_a_draft_purchase_order(): void
    {
        $vendor = Vendor::factory()->create();
        $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id, 'status' => PurchaseOrderStatus::Draft->value]);

        $this->expectException(ValidationException::class);

        app(VendorInvoiceService::class)->submit($po, $vendor, [
            'invoice_date' => now()->toDateString(),
            'amount' => 1000,
        ]);
    }

    // --- Staff review ------------------------------------------------------

    public function test_a_user_without_permission_cannot_see_the_vendor_invoice_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(StaffVendorInvoiceIndex::class)
            ->assertForbidden();
    }

    public function test_approving_a_vendor_invoice_posts_an_expense_and_locks_review(): void
    {
        $vendor = Vendor::factory()->create();
        $po = PurchaseOrder::factory()->create(['vendor_id' => $vendor->id, 'status' => PurchaseOrderStatus::Sent->value]);
        $invoice = VendorInvoice::factory()->create([
            'vendor_id' => $vendor->id,
            'purchase_order_id' => $po->id,
            'amount' => 8000,
        ]);
        $category = ExpenseCategory::factory()->create();

        Livewire::actingAs($this->staff)->test(StaffVendorInvoiceShow::class, ['vendorInvoice' => $invoice])
            ->call('toggleReviewForm')
            ->set('categoryId', (string) $category->id)
            ->set('method', 'bank')
            ->call('approve')
            ->assertHasNoErrors();

        $invoice->refresh();
        $this->assertSame(VendorInvoiceStatus::Approved, $invoice->status);
        $this->assertSame($this->staff->id, $invoice->reviewed_by);
        $this->assertNotNull($invoice->expense_id);
        $this->assertEquals(8000, $invoice->expense->amount);
        $this->assertSame($po->project_id, $invoice->expense->project_id);
    }

    public function test_rejecting_a_vendor_invoice_requires_a_reason_and_does_not_post_an_expense(): void
    {
        $invoice = VendorInvoice::factory()->create();

        Livewire::actingAs($this->staff)->test(StaffVendorInvoiceShow::class, ['vendorInvoice' => $invoice])
            ->call('toggleReviewForm')
            ->set('reviewNotes', '')
            ->call('reject')
            ->assertHasErrors(['reviewNotes' => 'required']);

        $invoice->refresh();
        $this->assertSame(VendorInvoiceStatus::Submitted, $invoice->status);

        Livewire::actingAs($this->staff)->test(StaffVendorInvoiceShow::class, ['vendorInvoice' => $invoice])
            ->call('toggleReviewForm')
            ->set('reviewNotes', 'Amount does not match the PO.')
            ->call('reject')
            ->assertHasNoErrors();

        $invoice->refresh();
        $this->assertSame(VendorInvoiceStatus::Rejected, $invoice->status);
        $this->assertNull($invoice->expense_id);
    }

    public function test_an_already_reviewed_invoice_cannot_be_reviewed_again(): void
    {
        $invoice = VendorInvoice::factory()->create(['status' => VendorInvoiceStatus::Approved->value]);

        Livewire::actingAs($this->staff)->test(StaffVendorInvoiceShow::class, ['vendorInvoice' => $invoice])
            ->call('toggleReviewForm')
            ->assertForbidden();
    }
}
