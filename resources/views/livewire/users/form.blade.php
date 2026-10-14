<div>
    <x-page-header :title='$user ? "Edit {$user->name}" : "New User"'>
        <x-slot:breadcrumbs>
            <x-breadcrumbs :items="['Users' => route('users.index'), ($user ? 'Edit' : 'New') => null]" />
        </x-slot:breadcrumbs>
    </x-page-header>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <div class="panel grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
            <x-input label="Name *" name="name" wire:model="name" />
            <x-input label="Email *" name="email" type="email" wire:model="email" />
            <x-input label="Phone" name="phone" wire:model="phone" />
            <label class="flex items-center gap-2 self-end pb-2">
                <input wire:model="is_active" type="checkbox" class="rounded border-ink-300 text-accent-600 shadow-sm focus:ring-accent-500">
                <span class="text-sm text-ink-700">Active</span>
            </label>
            <x-input
                :label="$user ? 'New password' : 'Password *'"
                name="password"
                type="password"
                wire:model="password"
                :placeholder="$user ? 'Leave blank to keep the current password' : ''"
            />
            <x-input label="Confirm password" name="password_confirmation" type="password" wire:model="password_confirmation" />
        </div>

        <div class="panel p-6">
            <h2 class="mb-4 text-sm font-semibold text-ink-700">Roles</h2>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($availableRoles as $roleName)
                    <label class="flex items-center gap-2">
                        <input wire:model="roles" value="{{ $roleName }}" type="checkbox" class="rounded border-ink-300 text-accent-600 shadow-sm focus:ring-accent-500">
                        <span class="text-sm text-ink-700">{{ $roleName }}</span>
                    </label>
                @endforeach
            </div>
            @error('roles') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <x-button type="submit">{{ $user ? 'Save changes' : 'Create user' }}</x-button>
            <x-button variant="secondary" href="{{ route('users.index') }}" wire:navigate>Cancel</x-button>
        </div>
    </form>
</div>
