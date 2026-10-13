<?php

namespace App\Livewire\Clients;

use App\Enums\ClientType;
use App\Enums\UserType;
use App\Models\Client;
use App\Models\User;
use App\Services\ClientService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
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

    /**
     * "Give this client portal access" — only offered when there's no
     * linked login yet (Client.user_id). Unlike the same checkbox on
     * Employees\Form, this needs no extra `create_users` gate: a
     * client-portal account never holds a Spatie role (it's ownership-
     * based access, ARCHITECTURE.md §5/§7), so the privilege it grants
     * is exactly "this one client can see their own records" — already
     * covered by the create/update permission this form requires below.
     */
    public bool $create_account = false;

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
            'email' => $this->create_account
                ? ['required', 'email', 'max:255', 'unique:users,email']
                : ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'site_address' => ['nullable', 'string', 'max:500'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save(ClientService $service, UserService $users): void
    {
        $data = $this->validate();
        $data['assigned_to'] = $data['assigned_to'] !== '' ? $data['assigned_to'] : null;

        $wantsAccount = $this->create_account && ! $this->client?->user_id;

        $client = DB::transaction(function () use ($service, $users, $data, $wantsAccount) {
            $client = $this->client
                ? $service->update($this->client, $data)
                : $service->create($data);

            if ($wantsAccount) {
                ['user' => $user, 'password' => $password] = $users->createLinkedAccount(
                    $client->name,
                    $client->email,
                    UserType::Client,
                );

                $client->update(['user_id' => $user->id]);

                // No outbound email in this app — handed back once, in
                // the session, so the Client Show page can display it
                // exactly once right after the redirect below.
                session()->flash('generated_password', $password);
                session()->flash('generated_password_for', $user->email);
            }

            return $client;
        });

        if ($this->client) {
            $message = 'Client updated.'.($wantsAccount ? ' Portal login created.' : '');
        } else {
            $message = "Client {$client->client_no} created.".($wantsAccount ? ' Portal login created.' : '');
        }

        $this->dispatch('toast', message: $message, type: 'success');
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
