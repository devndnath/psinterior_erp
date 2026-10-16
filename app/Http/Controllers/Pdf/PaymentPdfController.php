<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pdf\Concerns\RespondsWithPdf;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * See InvoicePdfController's docblock — same fix, same reason. Reached
 * via a redirect from Payments\Show::downloadPdf()/printPdf().
 */
class PaymentPdfController extends Controller
{
    use RespondsWithPdf;

    public function __invoke(Payment $payment, Request $request): Response
    {
        Gate::authorize('view', $payment);

        $payment->load(['project', 'client', 'invoice']);

        return $this->respondWithPdf(
            Pdf::loadView('pdf.payment', ['payment' => $payment]),
            "{$payment->payment_no}.pdf",
            $request,
        );
    }
}
