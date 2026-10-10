<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Owns the Invoice aggregate: the invoice itself and its line items.
 * Payments are a separate aggregate (PaymentService) that reaches back
 * into this one only through recalculatePaymentStatus(), so an invoice
 * never has to know how a payment was recorded, only what its current
 * paid total is.
 */
class InvoiceService
{
    public function __construct(protected NumberingService $numbering) {}

    public function create(Project $project, array $data, array $items = []): Invoice
    {
        return DB::transaction(function () use ($project, $data, $items) {
            $invoice = $project->invoices()->create([
                'invoice_no' => $this->numbering->next('INV'),
                'client_id' => $data['client_id'] ?? $project->client_id,
                'contract_id' => $data['contract_id'] ?? null,
                'type' => $data['type'] ?? \App\Enums\InvoiceType::Progress->value,
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'status' => InvoiceStatus::Draft->value,
            ]);

            $this->syncItems($invoice, $items);
            $this->recalculateTotals($invoice);

            return $invoice->fresh(['items']);
        });
    }

    /**
     * Refuses to touch a Paid invoice — once fully paid, its figures are
     * closed out and an edit would silently invalidate payments already
     * recorded against it. Guarded here (service layer), not only in the
     * policy, so a direct service call from a future module can't bypass
     * the rule either.
     */
    public function update(Invoice $invoice, array $data, array $items): Invoice
    {
        if ($invoice->status === InvoiceStatus::Paid) {
            throw new \RuntimeException('A paid invoice cannot be edited.');
        }

        return DB::transaction(function () use ($invoice, $data, $items) {
            $invoice->update([
                'contract_id' => $data['contract_id'] ?? $invoice->contract_id,
                'type' => $data['type'] ?? $invoice->type->value,
                'invoice_date' => $data['invoice_date'] ?? $invoice->invoice_date,
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'discount' => $data['discount'] ?? $invoice->discount,
                'tax' => $data['tax'] ?? $invoice->tax,
                'notes' => $data['notes'] ?? $invoice->notes,
            ]);

            $this->syncItems($invoice, $items);
            $this->recalculateTotals($invoice);

            return $invoice->fresh(['items']);
        });
    }

    public function issue(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => InvoiceStatus::Issued->value]);

        return $invoice->fresh();
    }

    /**
     * Only a Draft/Issued invoice with no payments recorded against it
     * can be cancelled — once money has come in, cancelling it would
     * leave an orphaned payment with nothing to reconcile against.
     */
    public function cancel(Invoice $invoice): Invoice
    {
        if ((float) $invoice->paid_amount > 0) {
            throw new \RuntimeException('An invoice with payments recorded against it cannot be cancelled.');
        }

        $invoice->update(['status' => InvoiceStatus::Cancelled->value]);

        return $invoice->fresh();
    }

    /**
     * Re-sums paid_amount from this invoice's payments and derives the
     * payment status from it. Called by PaymentService inside the same
     * transaction as a payment create/delete, so the two numbers never
     * drift apart. Never auto-downgrades a Draft or Cancelled invoice —
     * only Issued/PartiallyPaid/Paid move here.
     */
    public function recalculatePaymentStatus(Invoice $invoice): Invoice
    {
        $paid = (float) $invoice->payments()->sum('amount');

        $status = $invoice->status;

        if (! in_array($invoice->status, [InvoiceStatus::Draft, InvoiceStatus::Cancelled], true)) {
            if ($paid <= 0) {
                $status = InvoiceStatus::Issued;
            } elseif ($paid >= (float) $invoice->grand_total) {
                $status = InvoiceStatus::Paid;
            } else {
                $status = InvoiceStatus::PartiallyPaid;
            }
        }

        $invoice->update([
            'paid_amount' => $paid,
            'status' => $status->value,
        ]);

        return $invoice->fresh();
    }

    protected function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 1);
            $rate = (float) ($item['rate'] ?? 0);

            $invoice->items()->create([
                'description' => $item['description'],
                'qty' => $qty,
                'rate' => $rate,
                'amount' => round($qty * $rate, 2),
            ]);
        }
    }

    public function recalculateTotals(Invoice $invoice): Invoice
    {
        $subtotal = (float) $invoice->items()->sum('amount');
        $grandTotal = $subtotal - (float) $invoice->discount + (float) $invoice->tax;

        $invoice->update([
            'subtotal' => $subtotal,
            'grand_total' => max($grandTotal, 0),
        ]);

        return $invoice->fresh();
    }
}
