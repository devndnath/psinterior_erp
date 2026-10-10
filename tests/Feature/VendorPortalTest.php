<?php

namespace Tests\Feature;

use App\Livewire\Vendor\PurchaseOrders\Show as VendorPurchaseOrderShow;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Same shape as ClientPortalTest — see "Portal authorization model" in
 * PHASE11_SUMMARY.md. Asserts the positive case (a vendor CAN view
 * their own purchase order) explicitly, not only the negative case.
 */
class VendorPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function makeVendorUser(Vendor $vendor): User
    {
        $user = User::factory()->vendor()->create();
        $vendor->update(['user_id' => $user->id]);

        return $user;
    }

    public function test_a_vendor_can_view_their_own_purchase_order_but_not_another_vendors(): void
    {
        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();
        $userA = $this->makeVendorUser($vendorA);

        $poA = PurchaseOrder::factory()->create(['vendor_id' => $vendorA->id]);
        $poB = PurchaseOrder::factory()->create(['vendor_id' => $vendorB->id]);

        Livewire::actingAs($userA, 'vendor')->test(VendorPurchaseOrderShow::class, ['purchaseOrder' => $poA])
            ->assertOk()
            ->assertSee($poA->po_no);

        Livewire::actingAs($userA, 'vendor')->test(VendorPurchaseOrderShow::class, ['purchaseOrder' => $poB])
            ->assertForbidden();
    }

    public function test_a_vendor_portal_user_holds_no_spatie_permissions(): void
    {
        $vendor = Vendor::factory()->create();
        $user = $this->makeVendorUser($vendor);

        $this->assertCount(0, $user->getAllPermissions());
    }
}
