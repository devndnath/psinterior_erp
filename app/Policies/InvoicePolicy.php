<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() && $user->can('view_invoices');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->isStaff() && $user->can('view_invoices');
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->can('create_invoices');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->isStaff() && $user->can('edit_invoices') && $invoice->status !== InvoiceStatus::Paid;
    }

    /**
     * Paid invoices are never deleted (spec §78 rule 3) — enforced here
     * rather than as a DB constraint, matching how every other "can't
     * touch this once it reaches a certain state" rule in this codebase
     * lives in the policy layer.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->isStaff() && $user->can('delete_invoices') && $invoice->status !== InvoiceStatus::Paid;
    }

    /**
     * Issuing a Draft invoice is gated by the distinct `approve_invoice`
     * ability — mirrors ChangeOrderPolicy::recordDecision() /
     * QuotationPolicy's approve_quotation, a deliberately narrower gate
     * than general edit.
     */
    public function issue(User $user, Invoice $invoice): bool
    {
        return $user->isStaff() && $user->can('approve_invoice');
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->isStaff() && $user->can('edit_invoices');
    }
}
