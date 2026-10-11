<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * See InvoicePdfController's docblock — same fix, same reason. Reached
 * via a redirect from the staff PurchaseOrders\Show::downloadPdf().
 * (The vendor-portal download has its own controller/route — see
 * Vendor\PurchaseOrderPdfController — since that side authorizes by
 * ownership, not a staff Policy.)
 */
class PurchaseOrderPdfController extends Controller
{
    public function __invoke(PurchaseOrder $purchaseOrder): Response
    {
        Gate::authorize('view', $purchaseOrder);

        $purchaseOrder->load(['vendor', 'project', 'warehouse', 'items.product']);

        return Pdf::loadView('pdf.purchase-order', ['purchaseOrder' => $purchaseOrder])
            ->download("{$purchaseOrder->po_no}.pdf");
    }
}
