<?php

namespace App\Livewire\Vendor\PurchaseOrders;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.vendor-portal', ['title' => 'Purchase Orders'])]
#[Title('Purchase Orders')]
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

        return view('livewire.vendor.purchase-orders.index', [
            'purchaseOrders' => PurchaseOrder::where('vendor_id', $vendor->id)
                ->with('project')
                ->orderByDesc('date')
                ->paginate(10),
        ]);
    }
}
