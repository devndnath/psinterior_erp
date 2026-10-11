{{--
    Generic overlay modal — unlike <x-confirm-dialog> (which is purpose-
    built to call one $wire action on confirm), this is a plain reusable
    container any page can open for arbitrary body content. It is driven
    by its own local Alpine state, seeded from the `show` prop, so it does
    NOT assume `$wire` is in scope the way confirm-dialog does.

    Uncontrolled (a local trigger toggles it):
        <div x-data="{ open: false }">
            <button @click="open = true" class="btn-secondary">Open</button>
            <x-modal title="Details">
                ...body...
                <x-slot:footer>
                    <button @click="open = false" class="btn-secondary">Close</button>
                </x-slot:footer>
            </x-modal>
        </div>

    Controlled from Livewire (bind `show` to a wire:model boolean and let
    this component's own x-data read it on load; toggle it by dispatching
    a browser event, e.g. $this->dispatch('open-modal') from the
    component, with a matching x-on listener added where it's embedded):
        <x-modal :show="$showDetails" title="Details">...</x-modal>
--}}
@props(['show' => false, 'title' => null])

<div x-data="{ open: @js($show) }" @keydown.escape.window="open = false">
    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/50 p-4"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            @click.outside="open = false"
            {{ $attributes->merge(['class' => 'panel w-full max-w-lg p-6']) }}
        >
            <div class="mb-4 flex items-start justify-between gap-4">
                @if ($title)
                    <h3 class="text-base font-semibold text-ink-900">{{ $title }}</h3>
                @endif
                <button type="button" @click="open = false" class="shrink-0 text-ink-400 hover:text-ink-600" aria-label="Close">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <div>{{ $slot }}</div>

            @isset($footer)
                <div class="mt-6 flex justify-end gap-3 border-t border-ink-100 pt-4">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
