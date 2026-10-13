<?php

namespace App\Livewire\Vendor\Rfqs;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Ownership check, not a staff Policy — see "Portal authorization
 * model" in PHASE11_SUMMARY.md. There is no invited/eligible-vendor
 * list anywhere in this schema (confirmed before building this: no
 * pivot, no vendor_ids column) — VendorQuotationService::create() lets
 * ANY vendor quote against ANY purchase request once it's sent for
 * quotation, so this list is deliberately "open": every request that
 * has ever been sent for quotation, not just ones still awaiting a
 * decision, so a vendor can see the outcome of requests they quoted on.
 */
#[Layout('layouts.vendor-portal', ['title' => 'RFQs'])]
#[Title('RFQs')]
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

        $requests = PurchaseRequest::whereIn('status', [
            PurchaseRequestStatus::SentForQuotation->value,
            PurchaseRequestStatus::VendorSelected->value,
            PurchaseRequestStatus::PoCreated->value,
        ])
            ->with(['project', 'vendorQuotations' => fn ($q) => $q->where('vendor_id', $vendor->id)])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.vendor.rfqs.index', [
            'requests' => $requests,
        ]);
    }
}
