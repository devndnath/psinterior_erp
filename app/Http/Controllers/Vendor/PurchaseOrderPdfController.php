<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * See App\Http\Controllers\Pdf\InvoicePdfController's docblock for why
 * this exists as a plain route instead of returning the PDF straight from
 * a Livewire action. Ownership check, not a staff Policy — same pattern
 * as Vendor\PurchaseOrders\Show::mount() (PHASE11_SUMMARY.md's "Portal
 * authorization model"), duplicated here rather than shared because a
 * portal user holds zero Spatie permissions by design, so there's no
 * Gate ability to authorize against.
 */
class PurchaseOrderPdfController extends Controller
{
    public function __invoke(PurchaseOrder $purchaseOrder): Response
    {
        $vendor = Auth::guard('vendor')->user()->vendor;
        abort_unless($vendor, 403, 'No vendor record linked to this account.');
        abort_unless($purchaseOrder->vendor_id === $vendor->id, 403);

        $purchaseOrder->load(['vendor', 'project', 'warehouse', 'items.product']);

        return Pdf::loadView('pdf.purchase-order', ['purchaseOrder' => $purchaseOrder])
            ->download("{$purchaseOrder->po_no}.pdf");
    }
}
