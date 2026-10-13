<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Staff login management — the audit gap this phase closes (previously
 * the only staff account was the one created by
 * database/seeders/SuperAdminSeeder.php; every other login needed raw
 * tinker access). Only ever lists `user_type = Staff` rows
 * (User::scopeStaff) — Client/Vendor logins are a side effect of their
 * own Client/Vendor record, managed from those modules instead.
 */
#[Layout('layouts.admin', ['title' => 'Users'])]
#[Title('Users')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deleteUser(int $userId): void
    {
        $target = User::staff()->findOrFail($userId);
        Gate::authorize('delete', $target);

        $target->delete();

        $this->dispatch('toast', message: "User \"{$target->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        $users = User::staff()
            ->with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.users.index', ['users' => $users]);
    }
}
