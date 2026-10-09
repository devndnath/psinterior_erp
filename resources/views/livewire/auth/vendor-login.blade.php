<div>
    <h2 class="text-center text-lg font-semibold text-gray-900">Vendor Portal Sign In</h2>
    <p class="mt-1 text-center text-sm text-gray-500">View purchase orders, deliveries and payment status.</p>

    <form wire:submit="submit" class="mt-6 space-y-5">
        <div>
            <label for="email" class="form-label">Email</label>
            <input wire:model="email" id="email" type="email" autofocus autocomplete="username"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="form-label">Password</label>
            <input wire:model="password" id="password" type="password" autocomplete="current-password"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            @error('password') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            Remember me
        </label>

        <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="btn-primary w-full">
            <span wire:loading.remove wire:target="submit">Sign in</span>
            <span wire:loading wire:target="submit">Signing in…</span>
        </button>
    </form>
</div>
