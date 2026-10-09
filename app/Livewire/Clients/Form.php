<?php

namespace App\Livewire\Clients;

use App\Enums\ClientType;
use App\Models\Client;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Client $client = null;

    public string $name = '';
    public string $type = 'individual';
    public string $phone = '';
    public string $email = '';
    public string $company_name = '';
    public string $address = '';
    public string $billing_address = '';
    public string $site_address = '';
    public string $tax_id = '';
    public string $assigned_to = '';
    public string $notes = '';

    public function mount(?Client $client = null): void
    {
        $this->client = $client?->exists ? $client : null;

        Gate::authorize($this->client ? 'update' : 'create', $this->client ?? Client::class);

        if ($this->client) {
            $this->fill([
                'name' => $this->client->name,
                'type' => $this->client->type->value,
                'phone' => $this->client->phone,
                'email' => $this->client->email,
                'company_name' => $this->client->company_name,
                'address' => $this->client->address,
                'billing_address' => $this->client->billing_address,
                'site_address' => $this->client->site_address,
                'tax_id' => $this->client->tax_id,
                'assigned_to' => (string) $this->client->assigned_to,
                'notes' => $this->client->notes,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(ClientType::cases(), 'value'))],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'site_address' => ['nullable', 'string', 'max:500'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(ClientService $service): void
    {
        $data = $this->validate();
        $data['assigned_to'] = $data['assigned_to'] !== '' ? $data['assigned_to'] : null;

        if ($this->client) {
            $service->update($this->client, $data);
            $this->dispatch('toast', message: 'Client updated.', type: 'success');
            $this->redirectRoute('clients.show', ['client' => $this->client->id], navigate: true);

            return;
        }

        $client = $service->create($data);
        $this->dispatch('toast', message: "Client {$client->client_no} created.", type: 'success');
        $this->redirectRoute('clients.show', ['client' => $client->id], navigate: true);
    }

    #[Title('Client')]
    public function render()
    {
        return view('livewire.clients.form', [
            'types' => ClientType::options(),
            'assignees' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
