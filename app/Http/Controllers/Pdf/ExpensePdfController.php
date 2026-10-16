<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pdf\Concerns\RespondsWithPdf;
use App\Models\Expense;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * See InvoicePdfController's docblock — same fix, same reason. Reached
 * via a redirect from Expenses\Show::downloadPdf()/printPdf().
 */
class ExpensePdfController extends Controller
{
    use RespondsWithPdf;

    public function __invoke(Expense $expense, Request $request): Response
    {
        Gate::authorize('view', $expense);

        $expense->load(['project', 'category', 'vendor', 'createdBy']);

        return $this->respondWithPdf(
            Pdf::loadView('pdf.expense', ['expense' => $expense]),
            "{$expense->expense_no}.pdf",
            $request,
        );
    }
}
