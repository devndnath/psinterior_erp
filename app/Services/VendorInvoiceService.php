<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Enums\VendorInvoiceStatus;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\VendorInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Owns the vendor-submitted-invoice aggregate — closes the "vendors have
 * no way to submit their own invoice" audit gap (the vendor portal's
 * Ledger page has carried a documented "this is NOT a true vendor
 * invoicing system" disclaimer since Phase 11). A vendor files one of
 * these against their own Purchase Order; staff review it here.
 * Approving one creates the matching payable Expense row so it shows up
 * in the existing vendor Ledger view for free, rather than this feature
 * inventing a second, parallel ledger.
 */
class VendorInvoiceService
{
    public function __construct(
        protected NumberingService $numbering,
        protected ExpenseService $expenses,
    ) {}

    /**
     * The PO must actually have been placed with this vendor — not a
     * Draft nobody has sent yet, not a Cancelled one — and it must
     * belong to the submitting vendor. Both checked here, not just at
     * the Livewire layer, same discipline as VendorQuotationService::create().
     */
    public function submit(PurchaseOrder $po, Vendor $vendor, array $data): VendorInvoice
    {
        if ($po->vendor_id !== $vendor->id) {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'That purchase order does not belong to your company.',
            ]);
        }

        if (in_array($po->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Cancelled], true)) {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'This purchase order has not been placed yet, so an invoice cannot be submitted against it.',
            ]);
        }

        return $po->vendorInvoices()->create([
            'vi_no' => $this->numbering->next('VI'),
            'vendor_id' => $vendor->id,
            'vendor_invoice_no' => $data['vendor_invoice_no'] ?? null,
            'invoice_date' => $data['invoice_date'],
            'amount' => $data['amount'],
            'notes' => $data['notes'] ?? null,
            'status' => VendorInvoiceStatus::Submitted->value,
        ]);
    }

    /**
     * Approves the invoice and posts the matching payable Expense in one
     * transaction — $data carries the category/method a human has to
     * pick (there's no "default vendor-payment category" seeded, so
     * staff choose one here, same as the plain Expense form does).
     */
    public function approve(VendorInvoice $invoice, array $data, ?int $reviewerId): VendorInvoice
    {
        if ($invoice->status !== VendorInvoiceStatus::Submitted) {
            throw ValidationException::withMessages([
                'status' => 'This invoice has already been reviewed.',
            ]);
        }

        return DB::transaction(function () use ($invoice, $data, $reviewerId) {
            $expense = $this->expenses->create([
                'project_id' => $invoice->purchaseOrder->project_id,
                'category_id' => $data['category_id'],
                'vendor_id' => $invoice->vendor_id,
                'amount' => (float) $invoice->amount,
                'date' => $data['date'] ?? now()->toDateString(),
                'method' => $data['method'] ?? 'bank',
                'description' => "Vendor invoice {$invoice->vi_no}".
                    ($invoice->vendor_invoice_no ? " ({$invoice->vendor_invoice_no})" : '').
                    " for PO {$invoice->purchaseOrder->po_no}",
                'created_by' => $reviewerId,
            ]);

            $invoice->update([
                'status' => VendorInvoiceStatus::Approved->value,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'review_notes' => $data['review_notes'] ?? null,
                'expense_id' => $expense->id,
            ]);

            return $invoice->fresh(['expense']);
        });
    }

    public function reject(VendorInvoice $invoice, ?string $notes, ?int $reviewerId): VendorInvoice
    {
        if ($invoice->status !== VendorInvoiceStatus::Submitted) {
            throw ValidationException::withMessages([
                'status' => 'This invoice has already been reviewed.',
            ]);
        }

        $invoice->update([
            'status' => VendorInvoiceStatus::Rejected->value,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return $invoice->fresh();
    }
}
