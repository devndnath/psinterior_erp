<?php

namespace App\Http\Controllers\Pdf\Concerns;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared by every Pdf/*Controller: the same generated PDF can be either
 * force-downloaded (the existing "Download PDF" button) or streamed
 * inline (a "Print" button that opens it in a new browser tab, where the
 * browser's own PDF viewer provides the print affordance — no separate
 * print template or JS print library needed).
 *
 * Selected by an explicit `?print=1` query string the Livewire
 * printPdf()/downloadPdf() actions attach when they redirect here —
 * never a header or Accept-type sniff, so the same link always behaves
 * the same way regardless of browser quirks.
 *
 * $pdf is whatever Pdf::loadView(...) returns (Barryvdh\DomPDF\PDF) —
 * left untyped here since no other controller in this codebase types
 * that return value either, and the package isn't installed in this
 * sandbox to confirm the exact FQCN.
 */
trait RespondsWithPdf
{
    protected function respondWithPdf($pdf, string $filename, Request $request): Response
    {
        return $request->boolean('print')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }
}
