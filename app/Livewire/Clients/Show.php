<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Services\ClientService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Client 360 — assembles everything the ERP knows about one client
 * (ARCHITECTURE.md §6 / spec §6). Meetings sections are added as that
 * module ships in a later phase; Leads, Site Visits, Projects, Contacts,
 * Quotations, Contracts, Invoices and Payments exist now.
 */
#[Layout('layouts.admin')]
class Show extends Component
{
    public Client $client;

    public bool $showContactForm = false;
    public string $contact_name = '';
    public string $contact_designation = '';
    public string $contact_phone = '';
    public string $contact_email = '';
    public bool $contact_is_primary = false;

    /**
     * Set only immediately after Clients\Form creates a portal login
     * for this client — see Employees\Show's matching property for why
     * this is shown on screen once instead of emailed.
     */
    public ?string $generatedPassword = null;

    /**
     * Distinguishes "a brand-new portal login was just created" from "an
     * existing one's password was just reset" so the on-screen banner
     * below can word itself correctly — both reuse $generatedPassword.
     */
    public bool $passwordJustReset = false;

    public function mount(Client $client): void
    {
        Gate::authorize('view', $client);

        // Loaded here (not just render()) because mount() needs
        // $client->portalUser below, and Model::shouldBeStrict() outside
        // production throws on an unloaded lazy relation. Named
        // portalUser(), not user() — Client already has an unrelated
        // assignee() BelongsTo(User::class, 'assigned_to').
        $this->client = $client->load('portalUser');

        if (session('generated_password_for') === $this->client->portalUser?->email) {
            $this->generatedPassword = session()->pull('generated_password');
            session()->forget('generated_password_for');
        }
    }

    public function addContact(ClientService $service): void
    {
        Gate::authorize('update', $this->client);

        $data = $this->validate([
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_designation' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_is_primary' => ['boolean'],
        ]);

        $service->addContact($this->client, [
            'name' => $data['contact_name'],
            'designation' => $data['contact_designation'] ?: null,
            'phone' => $data['contact_phone'] ?: null,
            'email' => $data['contact_email'] ?: null,
            'is_primary' => $data['contact_is_primary'],
        ]);

        $this->reset(['contact_name', 'contact_designation', 'contact_phone', 'contact_email', 'contact_is_primary', 'showContactForm']);
        $this->client->refresh();

        $this->dispatch('toast', message: 'Contact added.', type: 'success');
    }

    public function removeContact(ClientService $service, int $contactId): void
    {
        Gate::authorize('update', $this->client);

        $service->removeContact($this->client, $contactId);
        $this->client->refresh();

        $this->dispatch('toast', message: 'Contact removed.', type: 'success');
    }

    /**
     * Suspend/restore this client's portal login — same `update`
     * permission (edit_clients) as every other action on this page, not
     * a separate user-management permission, since it's really "edit
     * this client's own portal access" rather than general user admin
     * (Client/Vendor logins are deliberately kept out of the staff-only
     * Users screen — see Users\Index's docblock).
     */
    public function togglePortalActive(UserService $service): void
    {
        Gate::authorize('update', $this->client);

        $portalUser = $this->client->portalUser;

        if (! $portalUser) {
            return;
        }

        $service->setActive($portalUser, ! $portalUser->is_active);
        $this->client->refresh();

        $this->dispatch('toast', message: $portalUser->is_active ? 'Portal access re-activated.' : 'Portal access deactivated.', type: 'success');
    }

    /**
     * Generates a fresh password for this client's portal login and
     * shows it on screen once — same no-outbound-email pattern as the
     * password shown right after Clients\Form first creates the login.
     */
    public function resetPortalPassword(UserService $service): void
    {
        Gate::authorize('update', $this->client);

        $portalUser = $this->client->portalUser;

        if (! $portalUser) {
            return;
        }

        $this->generatedPassword = $service->resetPassword($portalUser);
        $this->passwordJustReset = true;

        $this->dispatch('toast', message: 'Portal password reset.', type: 'success');
    }

    #[Title('Client')]
    public function render()
    {
        // Re-loaded on every render (not just in mount()), matching
        // Employees\Show's convention — see Vendors\Show::render()'s
        // comment for why.
        $this->client->load('portalUser');

        return view('livewire.clients.show', [
            'contacts' => $this->client->contacts,
            'leads' => $this->client->leads()->latest()->get(),
            'siteVisits' => $this->client->siteVisits()->latest('visit_date')->get(),
            'projects' => $this->client->projects()->latest()->get(),
            'quotations' => Auth::user()->can('view_quotations')
                ? $this->client->quotations()->latest()->get()
                : collect(),
            'contracts' => Auth::user()->can('view_contracts')
                ? $this->client->contracts()->latest()->get()
                : collect(),
            'invoices' => Auth::user()->can('view_invoices')
                ? $this->client->invoices()->latest()->get()
                : collect(),
            'payments' => Auth::user()->can('view_payments')
                ? $this->client->payments()->latest('date')->get()
                : collect(),
        ]);
    }
}
