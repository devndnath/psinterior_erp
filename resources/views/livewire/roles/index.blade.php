<div>
    <x-page-header title="Roles" description="The staff roles available when assigning permissions to a User, and what each one can currently do.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Roles' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \Spatie\Permission\Models\Role::class)
                <x-button href="{{ route('roles.create') }}" wire:navigate>+ New Role</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Permissions</th>
                <th>Users</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $role)
                <tr wire:key="role-{{ $role->id }}">
                    <td class="font-medium text-ink-900">
                        {{ $role->name }}
                        @if ($role->name === 'super-admin')
                            <span class="ml-2 inline-block rounded-full bg-ink-100 px-2 py-0.5 text-xs font-medium text-ink-600">bypasses all checks</span>
                        @endif
                    </td>
                    <td>{{ $role->permissions_count }}</td>
                    <td>{{ $role->users_count }}</td>
                    <td class="text-right">
                        {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                        <x-dropdown>
                        @can('update', $role)
                            <a href="{{ route('roles.edit', $role) }}" wire:navigate class="dropdown-item">Edit</a>
                        @endcan
                        @can('delete', $role)
                                                            <x-confirm-dialog
                                    title="Delete this role?"
                                    message="This will remove this role. This cannot be undone."
                                    action="deleteRole"
                                    :params="[$role->id]"
                                >
                                    <x-slot:trigger>
                                        <button type="button" class="dropdown-item-danger">Delete</button>
                                    </x-slot:trigger>
                                </x-confirm-dialog>
                        @endcan
                        </x-dropdown>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-table>
</div>
