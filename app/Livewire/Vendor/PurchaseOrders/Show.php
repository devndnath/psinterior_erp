<?php

namespace App\Livewire\Vendor\PurchaseOrders;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. Read-only — items, status, and the
 * Goods Receive history against it. Reuses the same
 * resources/views/pdf/purchase-order.blade.php the staff Show page uses.
 */
#[Layout('layouts.vendor-portal')]
class Show extends Component
{
    public PurchaseOrder $purchaseOrder;

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        $vendor = Auth::guard('vendor')->user()->vendor;
        abort_unless($vendor, 403, 'No vendor record linked to this account.');
        abort_unless($purchaseOrder->vendor_id === $vendor->id, 403);

        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * See App\Http\Controllers\Pdf\InvoicePdfController's docblock — same
     * fix, same reason: a binary PDF can't safely travel through
     * Livewire's own JSON response, so this redirects (full browser
     * navigation, not `navigate: true`) to a plain GET route instead. The
     * ownership check that used to live here now lives in
     * App\Http\Controllers\Vendor\PurchaseOrderPdfController, which this
     * route reaches.
     */
    public function downloadPdf()
    {
        return $this->redirect(route('vendor.purchase-orders.pdf', $this->purchaseOrder));
    }

    #[Title('Purchase Order')]
    public function render()
    {
        $this->purchaseOrder->load(['project', 'warehouse', 'items.product', 'goodsReceives.items']);

        return view('livewire.vendor.purchase-orders.show');
    }
}
