<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use App\Services\UserService;
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

    /**
     * Distinguishes "a brand-new portal login was just created" from "an
     * existing one's password was just reset" so the on-screen banner
     * below can word itself correctly — both reuse $generatedPassword.
     */
    public bool $passwordJustReset = false;

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

    /**
     * Suspend/restore this vendor's portal login — same `update`
     * permission (edit_vendors) as every other action on this page; see
     * Clients\Show::togglePortalActive()'s matching docblock for why
     * this isn't routed through the staff-only Users screen instead.
     */
    public function togglePortalActive(UserService $service): void
    {
        Gate::authorize('update', $this->vendor);

        $portalUser = $this->vendor->user;

        if (! $portalUser) {
            return;
        }

        $service->setActive($portalUser, ! $portalUser->is_active);
        $this->vendor->refresh();

        $this->dispatch('toast', message: $portalUser->is_active ? 'Portal access re-activated.' : 'Portal access deactivated.', type: 'success');
    }

    /**
     * Generates a fresh password for this vendor's portal login and
     * shows it on screen once — same no-outbound-email pattern as the
     * password shown right after Vendors\Form first creates the login.
     */
    public function resetPortalPassword(UserService $service): void
    {
        Gate::authorize('update', $this->vendor);

        $portalUser = $this->vendor->user;

        if (! $portalUser) {
            return;
        }

        $this->generatedPassword = $service->resetPassword($portalUser);
        $this->passwordJustReset = true;

        $this->dispatch('toast', message: 'Portal password reset.', type: 'success');
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
