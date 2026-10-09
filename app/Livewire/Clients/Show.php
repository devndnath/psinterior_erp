<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Client 360 — assembles everything the ERP knows about one client
 * (ARCHITECTURE.md §6 / spec §6). Projects, quotations, contracts,
 * invoices, payments and meetings sections are added as their modules
 * ship in later phases; only Leads, Site Visits and Contacts exist yet.
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

    public function mount(Client $client): void
    {
        Gate::authorize('view', $client);
        $this->client = $client;
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

    #[Title('Client')]
    public function render()
    {
        return view('livewire.clients.show', [
            'contacts' => $this->client->contacts,
            'leads' => $this->client->leads()->latest()->get(),
            'siteVisits' => $this->client->siteVisits()->latest('visit_date')->get(),
        ]);
    }
}
