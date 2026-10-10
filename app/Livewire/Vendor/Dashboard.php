<?php

namespace App\Livewire\Vendor;

use App\Enums\PurchaseOrderStatus;
use App\Models\GoodsReceive;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Vendor Portal dashboard — every figure scoped to the logged-in
 * vendor's OWN records (ownership check, not a staff Policy — see
 * "Portal authorization model" in PHASE11_SUMMARY.md).
 */
#[Layout('layouts.vendor-portal', ['title' => 'Dashboard'])]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::guard('vendor')->user()->vendor, 403, 'No vendor record linked to this account.');
    }

    public function render()
    {
        $vendor = Auth::guard('vendor')->user()->vendor;

        return view('livewire.vendor.dashboard', [
            'openPurchaseOrders' => PurchaseOrder::where('vendor_id', $vendor->id)
                ->whereIn('status', [PurchaseOrderStatus::Draft->value, PurchaseOrderStatus::Sent->value, PurchaseOrderStatus::PartiallyReceived->value])
                ->count(),
            'receivedThisMonth' => GoodsReceive::whereIn('purchase_order_id', PurchaseOrder::where('vendor_id', $vendor->id)->pluck('id'))
                ->whereBetween('received_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->count(),
        ]);
    }
}
