{{--
    Minimal single-line hover tooltip (premium-SaaS redesign spec:
    "Tooltips" under reusable components) — e.g. explaining a disabled
    action or an abbreviated column header. No positioning library; the
    bubble is a fixed dark chip in both light and dark mode (like most
    tooltip conventions, since it's a small floating overlay rather than
    page content) rather than ink-900, which would invert to near-white
    text-on-near-white under this app's dark-mode palette.

    <x-tooltip text="Only an admin can change this">
        <button type="button" disabled class="btn-secondary opacity-50">Approve</button>
    </x-tooltip>
--}}
@props(['text', 'position' => 'top'])

<span x-data="{ show: false }" class="relative inline-block" @mouseenter="show = true" @mouseleave="show = false" @focusin="show = true" @focusout="show = false">
    {{ $slot }}
    <span
        x-show="show"
        x-cloak
        x-transition.opacity
        role="tooltip"
        @class([
            'pointer-events-none absolute z-30 whitespace-nowrap rounded-md bg-black/90 px-2 py-1 text-xs font-medium text-white shadow-lg',
            'bottom-full left-1/2 mb-2 -translate-x-1/2' => $position === 'top',
            'top-full left-1/2 mt-2 -translate-x-1/2' => $position === 'bottom',
        ])
    >
        {{ $text }}
    </span>
</span>
