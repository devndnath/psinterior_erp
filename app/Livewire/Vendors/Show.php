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

    /**
     * Set only immediately after Vendors\Form creates a portal login
     * for this vendor — see Employees\Show's matching property for why
     * this is shown on screen once instead of emailed.
     */
    public ?string $generatedPassword = null;

    public function mount(Vendor $vendor): void
    {
        Gate::authorize('view', $vendor);

        // Loaded here (not just render()) because mount() needs
        // $vendor->user below, and Model::shouldBeStrict() outside
        // production throws on an unloaded lazy relation.
        $this->vendor = $vendor->load('user');

        if (session('generated_password_for') === $this->vendor->user?->email) {
            $this->generatedPassword = session()->pull('generated_password');
            session()->forget('generated_password_for');
        }
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
        // Re-loaded on every render (not just in mount()) — a
        // subsequent request within this component's lifecycle (e.g.
        // deleteVendor failing a Gate check) re-hydrates $vendor
        // without the relations mount() loaded, and the view reads
        // $vendor->user directly.
        $this->vendor->load('user');

        return view('livewire.vendors.show', [
            'products' => $this->vendor->products()->orderBy('name')->get(),
            'purchaseOrders' => $this->vendor->purchaseOrders()->latest()->limit(10)->get(),
        ]);
    }
}
