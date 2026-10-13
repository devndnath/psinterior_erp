<div>
    <x-page-header :title='$role ? "Edit {$role->name}" : "New Role"'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Roles' => route('roles.index'), ($role ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="panel grid grid-cols-1 gap-4 p-6">
            <x-input label="Role name *" name="name" wire:model="name" />
        </div>

        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-700">Permissions</h2>
            <div class="space-y-5">
                @foreach ($groupedPermissions as $module => $permissionNames)
                    <div>
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-ink-400">{{ str($module)->replace('_', ' ')->title() }}</h3>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ($permissionNames as $permissionName)
                                <label class="flex items-center gap-2">
                                    <input wire:model="permissions" value="{{ $permissionName }}" type="checkbox" class="rounded border-ink-300 text-accent-600 shadow-sm focus:ring-accent-500">
                                    <span class="text-sm text-ink-700">{{ $permissionName }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permissions') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $role ? 'Save changes' : 'Create role' }}</x-button>
            <x-button variant="secondary" href="{{ route('roles.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
