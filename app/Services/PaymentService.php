<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Payment aggregate. A payment optionally applies against one
 * Invoice — whenever it does, InvoiceService::recalculatePaymentStatus()
 * runs in the same transaction so the invoice's paid_amount/status is
 * never a step behind the payments actually recorded.
 */
class PaymentService
{
    public function __construct(
        protected NumberingService $numbering,
        protected InvoiceService $invoices,
    ) {}

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payment = Payment::create([
                'payment_no' => $this->numbering->next('PAY'),
                'client_id' => $data['client_id'],
                'project_id' => $data['project_id'],
                'invoice_id' => $data['invoice_id'] ?? null,
                'date' => $data['date'],
                'amount' => $data['amount'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'account' => $data['account'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($payment->invoice_id) {
                $invoice = Invoice::findOrFail($payment->invoice_id);
                $this->invoices->recalculatePaymentStatus($invoice);
            }

            return $payment->fresh();
        });
    }

    public function delete(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $invoiceId = $payment->invoice_id;

            $payment->delete();

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);

                if ($invoice) {
                    $this->invoices->recalculatePaymentStatus($invoice);
                }
            }
        });
    }
}
