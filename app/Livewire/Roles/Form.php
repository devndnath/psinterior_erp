<?php

namespace App\Livewire\Roles;

use App\Services\RoleService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?Role $role = null;

    public string $name = '';

    /** @var array<int,string> */
    public array $permissions = [];

    public function mount(?Role $role = null): void
    {
        $this->role = ($role?->exists && $role->guard_name === 'web') ? $role : null;

        Gate::authorize($this->role ? 'update' : 'create', $this->role ?? Role::class);

        if ($this->role) {
            $this->fill([
                'name' => $this->role->name,
                'permissions' => $this->role->permissions()->pluck('name')->all(),
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.($this->role?->id ?: 'NULL').',id,guard_name,web'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name,guard_name,web'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $service = app(RoleService::class);

        if ($this->role) {
            $service->update($this->role, $data['name'], $data['permissions']);
            $message = 'Role updated.';
        } else {
            $service->create($data['name'], $data['permissions']);
            $message = 'Role created.';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('roles.index', navigate: true);
    }

    /**
     * Groups every `web`-guard permission by module (the part before
     * the first underscore in a view/create/edit/delete_{module} name)
     * so the checklist reads as one block per module instead of one
     * long flat list. Non-CRUD abilities (approve_invoice,
     * manage_settings, etc.) fall under their own module name or
     * "Other" the same way RolePermissionSeeder groups them.
     */
    protected function groupedPermissions()
    {
        $names = Permission::where('guard_name', 'web')->orderBy('name')->pluck('name');

        $crudPrefixes = ['view_', 'create_', 'edit_', 'delete_'];

        return $names->groupBy(function (string $name) use ($crudPrefixes) {
            foreach ($crudPrefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    return substr($name, strlen($prefix));
                }
            }

            return 'other';
        })->sortKeys();
    }

    #[Title('Role')]
    public function render()
    {
        return view('livewire.roles.form', [
            'groupedPermissions' => $this->groupedPermissions(),
        ]);
    }
}
