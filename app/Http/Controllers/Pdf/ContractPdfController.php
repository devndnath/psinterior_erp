<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * See InvoicePdfController's docblock — same fix, same reason. Reached
 * via a redirect from Contracts\Show::downloadPdf().
 */
class ContractPdfController extends Controller
{
    public function __invoke(Contract $contract): Response
    {
        Gate::authorize('view', $contract);

        $contract->load(['project', 'client', 'items']);

        return Pdf::loadView('pdf.contract', ['contract' => $contract])
            ->download("{$contract->contract_no}.pdf");
    }
}
