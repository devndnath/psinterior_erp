<?php

namespace App\Livewire\Vendor\Ledger;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Read-only list of posted Expense rows against this vendor (closes the
 * Phase 8 `expenses.vendor_id` link with an actual consumer) — this is
 * "what's actually been paid or posted," not "what's pending." As of
 * Phase 17h there is a real vendor-invoice entity (App\Models\VendorInvoice,
 * reachable from this portal's "Invoices" screen) for invoices a vendor
 * has submitted and is still awaiting review; approving one is what
 * posts the Expense row that then shows up here. Ownership check, not a
 * staff Policy — see "Portal authorization model" in PHASE11_SUMMARY.md.
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
