<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pdf\Concerns\RespondsWithPdf;
use App\Models\Boq;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * See InvoicePdfController's docblock — same fix, same reason. Reached
 * via a redirect from Boqs\Show::downloadPdf()/printPdf().
 */
class BoqPdfController extends Controller
{
    use RespondsWithPdf;

    public function __invoke(Boq $boq, Request $request): Response
    {
        Gate::authorize('view', $boq);

        $boq->load(['project.client', 'items']);

        return $this->respondWithPdf(
            Pdf::loadView('pdf.boq', ['boq' => $boq]),
            "{$boq->boq_no}.pdf",
            $request,
        );
    }
}
