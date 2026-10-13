<?php

namespace App\Livewire\Vendor\Invoices;

use App\Models\VendorInvoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md.
 */
#[Layout('layouts.vendor-portal')]
class Show extends Component
{
    public VendorInvoice $vendorInvoice;

    public function mount(VendorInvoice $vendorInvoice): void
    {
        $vendor = Auth::guard('vendor')->user()->vendor;
        abort_unless($vendor, 403, 'No vendor record linked to this account.');
        abort_unless($vendorInvoice->vendor_id === $vendor->id, 403);

        $this->vendorInvoice = $vendorInvoice;
    }

    #[Title('Invoice')]
    public function render()
    {
        $this->vendorInvoice->load(['purchaseOrder']);

        return view('livewire.vendor.invoices.show', [
            'files' => $this->vendorInvoice->documents,
        ]);
    }
}
