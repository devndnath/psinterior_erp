<?php

namespace App\Livewire\Vendor\PurchaseOrders;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function downloadPdf()
    {
        $this->purchaseOrder->load(['vendor', 'project', 'warehouse', 'items.product']);

        return Pdf::loadView('pdf.purchase-order', ['purchaseOrder' => $this->purchaseOrder])
            ->download("{$this->purchaseOrder->po_no}.pdf");
    }

    #[Title('Purchase Order')]
    public function render()
    {
        $this->purchaseOrder->load(['project', 'warehouse', 'items.product', 'goodsReceives.items']);

        return view('livewire.vendor.purchase-orders.show');
    }
}
