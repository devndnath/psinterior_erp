{{--
    Reusable confirmation modal. Wraps a trigger and calls a Livewire
    method on confirm — used anywhere a destructive or hard-to-reverse
    action needs a deliberate second step (spec §57: "confirmation
    dialogs"). Example:

    <x-confirm-dialog
        title="Delete department?"
        message="This cannot be undone."
        action="deleteDepartment"
        :params="[$department->id]"
    >
        <x-slot:trigger>
            <button type="button" class="btn-danger">Delete</button>
        </x-slot:trigger>
    </x-confirm-dialog>
--}}
@props([
    'title' => 'Are you sure?',
    'message' => 'This action cannot be undone.',
    'action',
    'params' => [],
    'confirmText' => 'Confirm',
    'danger' => true,
])

<div x-data="{ open: false }" class="inline-block">
    <span @click="open = true">{{ $trigger }}</span>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        {{-- Literal black, not bg-gray-900 — a dimming scrim must stay dark
             in both themes, and gray-900 now flips to near-white in dark
             mode (same reasoning as <x-modal>'s backdrop). --}}
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @keydown.escape.window="open = false"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            @click.outside="open = false"
            class="card w-full max-w-sm p-6"
        >
            <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
            <p class="mt-2 text-sm text-gray-500">{{ $message }}</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="open = false" class="btn-secondary">Cancel</button>
                <button
                    type="button"
                    @click="open = false; $wire.{{ $action }}({{ implode(',', array_map('json_encode', $params)) }})"
                    class="{{ $danger ? 'btn-danger' : 'btn-primary' }}"
                >{{ $confirmText }}</button>
            </div>
        </div>
    </div>
</div>
