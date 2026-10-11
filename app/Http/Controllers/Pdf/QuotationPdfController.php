<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * See InvoicePdfController's docblock — same fix, same reason: a binary
 * PDF response can't safely travel through Livewire's `/livewire/update`
 * JSON cycle, so it's served from this plain GET route instead, reached
 * via a redirect (not an SPA `navigate`) from Quotations\Show::downloadPdf().
 */
class QuotationPdfController extends Controller
{
    public function __invoke(Quotation $quotation): Response
    {
        Gate::authorize('view', $quotation);

        $quotation->load(['project', 'client', 'items']);

        return Pdf::loadView('pdf.quotation', ['quotation' => $quotation])
            ->download("{$quotation->quotation_no}.pdf");
    }
}
