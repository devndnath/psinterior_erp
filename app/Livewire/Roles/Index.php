<?php

namespace App\Livewire\Roles;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

/**
 * Role & permission management — the audit gap this phase closes.
 * Previously the 11 staff roles and their permission sets existed only
 * as hand-edited PHP in database/seeders/RolePermissionSeeder.php, with
 * no screen to create a role or see/adjust what it can do.
 */
#[Layout('layouts.admin', ['title' => 'Roles'])]
#[Title('Roles')]
class Index extends Component
{
    public function mount(): void
    {
        Gate::authorize('viewAny', Role::class);
    }

    public function deleteRole(int $roleId): void
    {
        $role = Role::where('guard_name', 'web')->findOrFail($roleId);
        Gate::authorize('delete', $role);

        $role->delete();

        $this->dispatch('toast', message: "Role \"{$role->name}\" deleted.", type: 'success');
    }

    public function render()
    {
        return view('livewire.roles.index', [
            'roles' => Role::where('guard_name', 'web')
                ->withCount(['permissions', 'users'])
                ->orderBy('name')
                ->get(),
        ]);
    }
}
