<?php

namespace App\Livewire\Clients;

use App\Enums\ClientType;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin', ['title' => 'Clients'])]
#[Title('Clients')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $assignedTo = '';

    #[Url]
    public string $sort = 'created_at';

    #[Url]
    public string $direction = 'desc';

    public function mount(): void
    {
        Gate::authorize('viewAny', Client::class);
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'type', 'assignedTo'], true)) {
            $this->resetPage();
        }
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'type', 'assignedTo']);
    }

    public function deleteClient(int $clientId): void
    {
        $client = Client::findOrFail($clientId);
        Gate::authorize('delete', $client);

        $client->delete();

        $this->dispatch('toast', message: "Client {$client->client_no} deleted.", type: 'success');
    }

    public function render()
    {
        $clients = Client::query()
            ->with(['assignee'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('client_no', 'like', "%{$this->search}%")
            ))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->assignedTo, fn ($q) => $q->where('assigned_to', $this->assignedTo))
            ->orderBy($this->sort, $this->direction)
            ->paginate(15);

        return view('livewire.clients.index', [
            'clients' => $clients,
            'types' => ClientType::options(),
            'assignees' => User::staff()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
