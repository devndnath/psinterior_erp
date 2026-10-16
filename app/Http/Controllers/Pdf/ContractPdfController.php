<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pdf\Concerns\RespondsWithPdf;
use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * See InvoicePdfController's docblock — same fix, same reason. Reached
 * via a redirect from Contracts\Show::downloadPdf()/printPdf().
 */
class ContractPdfController extends Controller
{
    use RespondsWithPdf;

    public function __invoke(Contract $contract, Request $request): Response
    {
        Gate::authorize('view', $contract);

        $contract->load(['project', 'client', 'items']);

        return $this->respondWithPdf(
            Pdf::loadView('pdf.contract', ['contract' => $contract]),
            "{$contract->contract_no}.pdf",
            $request,
        );
    }
}
