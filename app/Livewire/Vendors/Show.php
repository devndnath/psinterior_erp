<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Vendor $vendor;

    public function mount(Vendor $vendor): void
    {
        Gate::authorize('view', $vendor);

        $this->vendor = $vendor;
    }

    public function deleteVendor(): void
    {
        Gate::authorize('delete', $this->vendor);

        $code = $this->vendor->vendor_code;
        $this->vendor->delete();

        $this->dispatch('toast', message: "Vendor {$code} deleted.", type: 'success');
        $this->redirectRoute('vendors.index', navigate: true);
    }

    #[Title('Vendor')]
    public function render()
    {
        return view('livewire.vendors.show', [
            'products' => $this->vendor->products()->orderBy('name')->get(),
            'purchaseOrders' => $this->vendor->purchaseOrders()->latest()->limit(10)->get(),
        ]);
    }
}
