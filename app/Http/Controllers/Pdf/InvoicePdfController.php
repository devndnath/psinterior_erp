<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pdf\Concerns\RespondsWithPdf;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * A plain, non-Livewire GET route for the actual PDF bytes.
 *
 * Livewire's component actions used to call Pdf::loadView(...)->download()
 * directly and return the binary Response from the action itself. Every
 * Livewire action response — even one your action method returns — still
 * has to round-trip through the `/livewire/update` endpoint's own
 * json_encode() of the component's dehydrated state, and raw PDF bytes are
 * binary, not valid UTF-8 text, so that json_encode() call reliably threw
 * the same "Malformed UTF-8 characters, possibly incorrectly encoded"
 * InvalidArgumentException this whole fix series has been chasing — this
 * one wasn't about corrupted data at all, unlike the Model::retrieved() /
 * Setting cache fixes that came before it.
 *
 * The fix is architectural, not a scrub: a real file download can never
 * safely travel through Livewire's AJAX/JSON cycle, so the binary response
 * now lives on an ordinary route instead. The Livewire action
 * (Show::downloadPdf()) does nothing but `$this->redirect(...)` here —
 * without `navigate: true`, so the browser performs a normal top-level GET
 * instead of Livewire's SPA-style fetch navigation — and the browser's own
 * handling of the Content-Disposition header does the rest.
 */
class InvoicePdfController extends Controller
{
    use RespondsWithPdf;

    public function __invoke(Invoice $invoice, Request $request): Response
    {
        Gate::authorize('view', $invoice);

        $invoice->load(['project', 'client', 'items']);

        return $this->respondWithPdf(
            Pdf::loadView('pdf.invoice', ['invoice' => $invoice]),
            "{$invoice->invoice_no}.pdf",
            $request,
        );
    }
}
