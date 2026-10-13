<?php

namespace App\Livewire\Vendor\Invoices;

use App\Models\VendorInvoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. A vendor's own submission history,
 * every status included (Submitted/Approved/Rejected) — unlike the
 * client Selections portal there's no draft state to hide here, a
 * vendor invoice is always "finished" the moment it's filed.
 */
#[Layout('layouts.vendor-portal', ['title' => 'Invoices'])]
#[Title('Invoices')]
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

        return view('livewire.vendor.invoices.index', [
            'invoices' => VendorInvoice::where('vendor_id', $vendor->id)
                ->with('purchaseOrder')
                ->orderByDesc('created_at')
                ->paginate(10),
        ]);
    }
}
