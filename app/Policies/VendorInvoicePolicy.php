<?php

namespace App\Policies;

use App\Enums\VendorInvoiceStatus;
use App\Models\User;
use App\Models\VendorInvoice;

/**
 * The vendor-submission side of this feature never goes through this
 * Policy — a vendor files one from the portal via an ownership check in
 * the Livewire component, same "Portal authorization model" as every
 * other vendor/client screen (PHASE11_SUMMARY.md). This Policy only
 * gates the staff review side.
 */
class VendorInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_vendor_invoices');
    }

    public function view(User $user, VendorInvoice $vendorInvoice): bool
    {
        return $user->isStaff() && $user->can('view_vendor_invoices');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_vendor_invoices');
    }

    public function delete(User $user, VendorInvoice $vendorInvoice): bool
    {
        // Once approved, the invoice has already posted an Expense — it
        // isn't deleted out from under that record. Reject a submission
        // instead if it was wrong.
        return $user->isStaff()
            && $user->can('delete_vendor_invoices')
            && $vendorInvoice->status !== VendorInvoiceStatus::Approved;
    }

    /**
     * Approve/reject — a managerial decision distinct from plain
     * view/edit, same split as DesignPolicy::approve()/ChangeOrderPolicy's
     * recordDecision(), so it's gated on its own seeded ability rather
     * than edit_vendor_invoices.
     */
    public function review(User $user, VendorInvoice $vendorInvoice): bool
    {
        return $user->isStaff()
            && $user->can('approve_vendor_invoice')
            && $vendorInvoice->status === VendorInvoiceStatus::Submitted;
    }
}
