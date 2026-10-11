<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * See App\Http\Controllers\Pdf\InvoicePdfController's docblock for why
 * this exists as a plain route instead of returning the PDF straight from
 * a Livewire action. Ownership check, not a staff Policy — same pattern
 * as Client\Invoices\Show::mount() (PHASE11_SUMMARY.md's "Portal
 * authorization model"), duplicated here rather than shared because a
 * portal user holds zero Spatie permissions by design, so there's no
 * Gate ability to authorize against.
 */
class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice): Response
    {
        $client = Auth::guard('client')->user()->client;
        abort_unless($client, 403, 'No client record linked to this account.');
        abort_unless($invoice->client_id === $client->id, 403);

        $invoice->load(['project', 'client', 'items']);

        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice])
            ->download("{$invoice->invoice_no}.pdf");
    }
}
