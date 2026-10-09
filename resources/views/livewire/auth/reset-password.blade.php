<div>
    <h2 class="text-center text-lg font-semibold text-gray-900">Set a new password</h2>

    <form wire:submit="resetPassword" class="mt-6 space-y-5">
        <div>
            <label for="email" class="form-label">Email</label>
            <input wire:model="email" id="email" type="email" autofocus autocomplete="username"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="form-label">New password</label>
            <input wire:model="password" id="password" type="password" autocomplete="new-password"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
        </div>

        <button type="submit" wire:loading.attr="disabled" class="btn-primary w-full">
            Reset password
        </button>
    </form>
</div>
