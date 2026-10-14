<div>
    <x-page-header title="Users" description="Staff logins and the roles each one holds. Client and Vendor portal logins are managed from their own Client/Vendor record.">
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Users' => null]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            @can('create', \App\Models\User::class)
                <x-button href="{{ route('users.create') }}" wire:navigate>+ New User</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="panel mb-4 flex flex-wrap items-end gap-4 p-4">
        <div class="min-w-[220px] flex-1">
            <x-input label="Search" name="search" wire:model.live.debounce.400ms="search" placeholder="Name or email…" />
        </div>
    </div>

    @if ($users->isEmpty())
        <div class="panel overflow-hidden">
            <x-empty-state
                title="No users found"
                description="Create a staff login to give someone else access to this system."
            />
        </div>
    @else
        <x-table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $staffUser)
                    <tr wire:key="user-{{ $staffUser->id }}">
                        <td class="font-medium text-ink-900">{{ $staffUser->name }}</td>
                        <td>{{ $staffUser->email }}</td>
                        <td>
                            @forelse ($staffUser->roles as $role)
                                <span class="mr-1 inline-block rounded-full bg-accent-50 px-2 py-0.5 text-xs font-medium text-accent-700">{{ $role->name }}</span>
                            @empty
                                <span class="text-ink-400">—</span>
                            @endforelse
                        </td>
                        <td>
                            <x-status-badge :label="$staffUser->is_active ? 'Active' : 'Inactive'" :color="$staffUser->is_active ? 'green' : 'gray'" />
                        </td>
                        <td class="text-right">
                            {{-- Phase 5 (premium-SaaS redesign): a single "..." action menu instead of several separate text links in every row. --}}
                            <x-dropdown>
                            @can('update', $staffUser)
                                <a href="{{ route('users.edit', $staffUser) }}" wire:navigate class="dropdown-item">Edit</a>
                            @endcan
                            @can('delete', $staffUser)
                                                                    <x-confirm-dialog
                                        title="Delete this user?"
                                        message="This will remove this user's login. This cannot be undone."
                                        action="deleteUser"
                                        :params="[$staffUser->id]"
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
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>
