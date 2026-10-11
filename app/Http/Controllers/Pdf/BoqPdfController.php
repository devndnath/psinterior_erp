<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Boq;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * See InvoicePdfController's docblock — same fix, same reason. Reached
 * via a redirect from Boqs\Show::downloadPdf().
 */
class BoqPdfController extends Controller
{
    public function __invoke(Boq $boq): Response
    {
        Gate::authorize('view', $boq);

        $boq->load(['project.client', 'items']);

        return Pdf::loadView('pdf.boq', ['boq' => $boq])
            ->download("{$boq->boq_no}.pdf");
    }
}
