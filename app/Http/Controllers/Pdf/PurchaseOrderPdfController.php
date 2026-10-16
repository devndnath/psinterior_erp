<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pdf\Concerns\RespondsWithPdf;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * See InvoicePdfController's docblock — same fix, same reason. Reached
 * via a redirect from the staff PurchaseOrders\Show::downloadPdf()/
 * printPdf(). (The vendor-portal download has its own controller/
 * route — see Vendor\PurchaseOrderPdfController — since that side
 * authorizes by ownership, not a staff Policy.)
 */
class PurchaseOrderPdfController extends Controller
{
    use RespondsWithPdf;

    public function __invoke(PurchaseOrder $purchaseOrder, Request $request): Response
    {
        Gate::authorize('view', $purchaseOrder);

        $purchaseOrder->load(['vendor', 'project', 'warehouse', 'items.product']);

        return $this->respondWithPdf(
            Pdf::loadView('pdf.purchase-order', ['purchaseOrder' => $purchaseOrder]),
            "{$purchaseOrder->po_no}.pdf",
            $request,
        );
    }
}
