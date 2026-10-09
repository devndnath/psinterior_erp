<div>
    <h2 class="text-center text-lg font-semibold text-gray-900">Forgot your password?</h2>
    <p class="mt-1 text-center text-sm text-gray-500">Enter your email and we'll send you a reset link.</p>

    @if ($status)
        <div class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ $status }}</div>
    @endif

    <form wire:submit="sendResetLink" class="mt-6 space-y-5">
        <div>
            <label for="email" class="form-label">Email</label>
            <input wire:model="email" id="email" type="email" autofocus autocomplete="username"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" class="btn-primary w-full">
            Email password reset link
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" wire:navigate class="text-brand-600 hover:text-brand-500">Back to sign in</a>
    </p>
</div>
