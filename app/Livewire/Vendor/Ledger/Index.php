<?php

namespace App\Livewire\Vendor\Ledger;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Read-only list of Expense rows against this vendor (closes the
 * Phase 8 `expenses.vendor_id` link with an actual consumer). This is
 * NOT a true vendor invoicing/payment-status system — there is no
 * vendor-invoice entity anywhere in this schema, so this is the
 * closest honest approximation to ARCHITECTURE.md's "invoices, payment
 * status" vendor-portal description available without inventing one;
 * see "Known issues" in PHASE11_SUMMARY.md. Ownership check, not a
 * staff Policy — see "Portal authorization model" there too.
 */
#[Layout('layouts.vendor-portal', ['title' => 'Ledger'])]
#[Title('Ledger')]
class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(Auth::guard('vendor')->user()->vendor, 403, 'No vendor record linked to this account.');
    }

    public function render()
    {
        $vendor = Auth::guard('vendor')->user()->vendor;

        return view('livewire.vendor.ledger.index', [
            'expenses' => Expense::where('vendor_id', $vendor->id)
                ->with('project')
                ->orderByDesc('date')
                ->paginate(15),
        ]);
    }
}
