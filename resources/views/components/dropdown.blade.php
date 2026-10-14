{{--
    Compact "..." action menu for table rows and card headers (premium-
    SaaS redesign spec: "use a clean action menu for secondary actions
    instead of showing too many icons in every row"). Self-contained
    Alpine state, same open/click-outside/escape pattern already used by
    components/modal.blade.php and components/confirm-dialog.blade.php.

    Put plain <a>/<button> tags styled with the .dropdown-item /
    .dropdown-item-danger classes (app.css) in the default slot:

    <x-dropdown>
        <a href="{{ route('leads.edit', $lead) }}" wire:navigate class="dropdown-item">Edit</a>
        <x-confirm-dialog title="Delete this lead?" action="deleteLead" :params="[$lead->id]">
            <x-slot:trigger>
                <button type="button" class="dropdown-item-danger">Delete</button>
            </x-slot:trigger>
        </x-confirm-dialog>
    </x-dropdown>

    `align="left"` opens the panel from the trigger's left edge instead
    of the default right edge — use it near a layout's right margin
    where a right-aligned panel would overflow off-screen.
--}}
@props(['align' => 'right'])

<div x-data="{ open: false }" class="relative inline-block text-left" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" class="rounded-lg p-1.5 text-ink-400 transition hover:bg-ink-50 hover:text-ink-700" aria-label="Open actions menu" aria-haspopup="true" :aria-expanded="open">
        <x-icon name="more-horizontal" class="h-4 w-4" />
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        {{--
            Deliberately no @click="open = false" here: a menu item can be
            an <x-confirm-dialog> trigger, whose actual modal renders as a
            DOM descendant of this panel. Auto-closing on any click would
            set this panel's x-show to false and hide that modal along
            with it. Click-outside and escape (above) still close the
            menu for the common case; a plain navigating link/button
            closes it implicitly by leaving the page or triggering a
            Livewire re-render of the row.
        --}}
        {{ $attributes->merge(['class' => ($align === 'left' ? 'left-0 origin-top-left' : 'right-0 origin-top-right').' absolute z-20 mt-1 w-44 rounded-lg border border-ink-200 bg-surface py-1 shadow-lg focus:outline-none']) }}
    >
        {{ $slot }}
    </div>
</div>
